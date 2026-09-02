<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\UsuarioRepository;
use App\Repositories\PeriodoRepository;
use App\Repositories\SolicitacaoRepository;
use App\Services\LimiteService;

Auth::requirePerfil(['ADMIN', 'COORDENADOR']);

$usuarioRepo = new UsuarioRepository();
$termo = inputGet('q', '');
$resultadosBusca = $termo ? $usuarioRepo->pesquisar($termo, null, 10) : [];

$usuarioId = inputGet('usuario_id') ? (int) inputGet('usuario_id') : null;
$periodoRepo = new PeriodoRepository();
$periodoId = inputGet('periodo_id') ? (int) inputGet('periodo_id') : null;
$periodos = $periodoRepo->listarTodos();

$usuario = null;
$resumoLimite = null;
$historicoCompleto = [];
$totais = null;

if ($usuarioId) {
    $usuario = $usuarioRepo->buscarPorId($usuarioId);
    $periodoAlvo = $periodoId ? $periodoRepo->buscarPorId($periodoId) : $periodoRepo->periodoAtivo();

    if ($usuario && $periodoAlvo) {
        $limiteService = new LimiteService();
        $resumoLimite = $limiteService->resumoUsuarioPeriodo($usuarioId, (int)$periodoAlvo['id']);
    }

    $solicitacaoRepo = new SolicitacaoRepository();
    $historicoCompleto = $solicitacaoRepo->listarMinhas($usuarioId);

    $db = \App\Core\Database::connection();
    $stmtTotais = $db->prepare("SELECT
        SUM(tipo='TROCA') AS total_trocas,
        SUM(tipo='PASSAGEM' ) AS total_passagens_realizadas
        FROM solicitacoes WHERE solicitante_id = :id");
    $stmtTotais->execute(['id' => $usuarioId]);
    $totais = $stmtTotais->fetch();

    $stmtRecebidas = $db->prepare("SELECT COUNT(*) FROM passagens WHERE quem_recebeu_id = :id");
    $stmtRecebidas->execute(['id' => $usuarioId]);
    $totais['total_passagens_recebidas'] = (int)$stmtRecebidas->fetchColumn();

    $stmtExcecoes = $db->prepare("SELECT COUNT(*) FROM excecoes_limite WHERE usuario_id = :id AND status='AUTORIZADA'");
    $stmtExcecoes->execute(['id' => $usuarioId]);
    $totais['total_excecoes'] = (int)$stmtExcecoes->fetchColumn();
}

view('relatorios/individual', compact('termo', 'resultadosBusca', 'usuario', 'resumoLimite', 'historicoCompleto', 'totais', 'periodos', 'periodoId'));
