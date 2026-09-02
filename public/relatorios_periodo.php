<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Repositories\PeriodoRepository;
use App\Repositories\SetorRepository;
use App\Services\LimiteService;

Auth::requirePerfil(['ADMIN', 'COORDENADOR']);

$periodoRepo = new PeriodoRepository();
$periodos = $periodoRepo->listarTodos();
$periodoId = inputGet('periodo_id') ? (int) inputGet('periodo_id') : null;
$periodo = $periodoId ? $periodoRepo->buscarPorId($periodoId) : $periodoRepo->periodoAtivo();

$setorRepo = new SetorRepository();
$setorIds = Auth::isCoordenador() ? $setorRepo->idsPorCoordenador(Auth::id()) : null;

$linhas = [];
if ($periodo) {
    $db = Database::connection();
    $sql = "SELECT u.id, u.nome, u.matricula, s.nome AS setor_nome FROM usuarios u
            JOIN setores s ON s.id = u.setor_id WHERE u.status = 'ATIVO'";
    $params = [];
    if ($setorIds) {
        $in = implode(',', array_fill(0, count($setorIds), '?'));
        $sql .= " AND u.setor_id IN ($in)";
        $params = $setorIds;
    }
    $sql .= " ORDER BY u.nome";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();

    $limiteService = new LimiteService();
    $stmtPassou = $db->prepare("SELECT COUNT(*) FROM solicitacoes s JOIN passagens p ON p.solicitacao_id = s.id
        WHERE p.quem_passou_id = :id AND s.periodo_id = :p AND s.status != 'CANCELADA' AND s.status != 'RECUSADA' AND s.status != 'REPROVADA'");
    $stmtRecebeu = $db->prepare("SELECT COUNT(*) FROM passagens p JOIN solicitacoes s ON s.id = p.solicitacao_id
        WHERE p.quem_recebeu_id = :id AND s.periodo_id = :p");
    $stmtPendenteFlit = $db->prepare("SELECT COUNT(*) FROM solicitacoes WHERE solicitante_id = :id AND periodo_id = :p AND flit_status = 'PENDENTE_FLIT'");

    foreach ($usuarios as $u) {
        $trocaInfo = $limiteService->calcular((int)$u['id'], 'TROCA', (int)$periodo['id']);
        $passagemInfo = $limiteService->calcular((int)$u['id'], 'PASSAGEM', (int)$periodo['id']);

        $stmtPassou->execute(['id' => $u['id'], 'p' => $periodo['id']]);
        $passou = (int)$stmtPassou->fetchColumn();
        $stmtRecebeu->execute(['id' => $u['id'], 'p' => $periodo['id']]);
        $recebeu = (int)$stmtRecebeu->fetchColumn();
        $stmtPendenteFlit->execute(['id' => $u['id'], 'p' => $periodo['id']]);
        $pendenteFlit = (int)$stmtPendenteFlit->fetchColumn();

        if ($trocaInfo['utilizadas'] === 0 && $passagemInfo['utilizadas'] === 0 && $passou === 0 && $recebeu === 0) {
            continue; // omite quem não teve nenhuma movimentação no período
        }

        $linhas[] = [
            'usuario' => $u,
            'troca' => $trocaInfo,
            'passagem' => $passagemInfo,
            'passou' => $passou,
            'recebeu' => $recebeu,
            'pendente_flit' => $pendenteFlit,
        ];
    }
}

view('relatorios/periodo', compact('periodos', 'periodo', 'linhas'));
