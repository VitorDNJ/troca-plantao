<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\TrocaRepository;
use App\Repositories\HistoricoRepository;
use App\Repositories\SetorRepository;
use App\Services\LimiteService;
use App\Services\FluxoAprovacaoService;
use App\Services\TransicaoInvalidaException;

Auth::requireLogin();

$id = (int) inputGet('id', 0);
$solicitacaoRepo = new SolicitacaoRepository();
$sol = $solicitacaoRepo->buscarPorId($id);

if (!$sol || $sol['tipo'] !== 'TROCA') {
    http_response_code(404);
    die('Solicitação não encontrada.');
}

$trocaRepo = new TrocaRepository();
$troca = $trocaRepo->buscarPorSolicitacao($id);

// Controle de acesso: solicitante, o outro colaborador envolvido, coordenador do setor, ou admin.
$setorRepo = new SetorRepository();
$souSolicitante = (int)$sol['solicitante_id'] === Auth::id();
$souOutro = (int)$troca['outro_usuario_id'] === Auth::id();

$setorIdsCoordenador = Auth::isCoordenador() ? $setorRepo->idsPorCoordenador(Auth::id()) : [];
$usuarioRepo = new \App\Repositories\UsuarioRepository();
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
            flashMessage('success', 'Troca aceita! Enviada para aprovação do coordenador.');
        } elseif ($acao === 'recusar' && $souOutro) {
            $fluxo->recusar($id, Auth::id(), inputPost('observacao_recusa'));
            flashMessage('success', 'Troca recusada.');
        } elseif ($acao === 'cancelar' && $souSolicitante) {
            $fluxo->cancelar($id, Auth::id());
            flashMessage('success', 'Solicitação cancelada.');
        } else {
            flashMessage('danger', 'Ação não permitida.');
        }
    } catch (TransicaoInvalidaException $e) {
        flashMessage('danger', $e->getMessage());
    }
    redirect(url('trocas_ver.php?id=' . $id));
}

view('trocas/ver', compact('sol', 'troca', 'historico', 'souSolicitante', 'souOutro'));
