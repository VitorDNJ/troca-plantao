<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\PassagemRepository;
use App\Repositories\HistoricoRepository;
use App\Repositories\SetorRepository;
use App\Repositories\UsuarioRepository;
use App\Services\FluxoAprovacaoService;
use App\Services\TransicaoInvalidaException;

Auth::requireLogin();

$id = (int) inputGet('id', 0);
$solicitacaoRepo = new SolicitacaoRepository();
$sol = $solicitacaoRepo->buscarPorId($id);

if (!$sol || $sol['tipo'] !== 'PASSAGEM') {
    http_response_code(404);
    die('Solicitação não encontrada.');
}

$passagemRepo = new PassagemRepository();
$passagem = $passagemRepo->buscarPorSolicitacao($id);

$souSolicitante = (int)$sol['solicitante_id'] === Auth::id();
$souOutro = (int)$passagem['quem_recebeu_id'] === Auth::id();

$setorRepo = new SetorRepository();
$setorIdsCoordenador = Auth::isCoordenador() ? $setorRepo->idsPorCoordenador(Auth::id()) : [];
$usuarioRepo = new UsuarioRepository();
$solicitanteUsuario = $usuarioRepo->buscarPorId((int)$sol['solicitante_id']);
$souCoordenadorDoSetor = in_array((int)$solicitanteUsuario['setor_id'], $setorIdsCoordenador, true);

if (!$souSolicitante && !$souOutro && !$souCoordenadorDoSetor && !Auth::isAdmin()) {
    http_response_code(403);
    die('Você não tem permissão para visualizar esta solicitação.');
}

$historicoRepo = new HistoricoRepository();
$historico = $historicoRepo->listarPorSolicitacao($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();
    $acao = inputPost('acao');
    $fluxo = new FluxoAprovacaoService();
    try {
        if ($acao === 'aceitar' && $souOutro) {
            $fluxo->aceitar($id, Auth::id());
            flashMessage('success', 'Passagem aceita! Enviada para aprovação do coordenador.');
        } elseif ($acao === 'recusar' && $souOutro) {
            $fluxo->recusar($id, Auth::id(), inputPost('observacao_recusa'));
            flashMessage('success', 'Passagem recusada.');
        } elseif ($acao === 'cancelar' && $souSolicitante) {
            $fluxo->cancelar($id, Auth::id());
            flashMessage('success', 'Solicitação cancelada.');
        } else {
            flashMessage('danger', 'Ação não permitida.');
        }
    } catch (TransicaoInvalidaException $e) {
        flashMessage('danger', $e->getMessage());
    }
    redirect(url('passagens_ver.php?id=' . $id));
}

view('passagens/ver', compact('sol', 'passagem', 'historico', 'souSolicitante', 'souOutro'));
