<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\TrocaRepository;
use App\Repositories\PassagemRepository;
use App\Repositories\HistoricoRepository;
use App\Repositories\SetorRepository;
use App\Repositories\ExcecaoRepository;
use App\Services\LimiteService;
use App\Services\FluxoAprovacaoService;
use App\Services\TransicaoInvalidaException;

Auth::requirePerfil(['COORDENADOR']);

$id = (int) inputGet('id', 0);
$solicitacaoRepo = new SolicitacaoRepository();
$sol = $solicitacaoRepo->buscarPorId($id);

if (!$sol) {
    http_response_code(404);
    die('Solicitação não encontrada.');
}

// Coordenador só pode acessar solicitações de setores sob sua responsabilidade.
$setorRepo = new SetorRepository();
$setorIds = $setorRepo->idsPorCoordenador(Auth::id());
$usuarioRepo = new \App\Repositories\UsuarioRepository();
$solicitanteUsuario = $usuarioRepo->buscarPorId((int)$sol['solicitante_id']);
if (!in_array((int)$solicitanteUsuario['setor_id'], $setorIds, true)) {
    http_response_code(403);
    die('Você não tem permissão para acessar solicitações deste setor.');
}

$detalhe = null;
if ($sol['tipo'] === 'TROCA') {
    $detalhe = (new TrocaRepository())->buscarPorSolicitacao($id);
} else {
    $detalhe = (new PassagemRepository())->buscarPorSolicitacao($id);
}

$limiteService = new LimiteService();
$situacaoLimite = $limiteService->calcular((int)$sol['solicitante_id'], $sol['tipo'], (int)$sol['periodo_id'], $id);

$excecaoInfo = null;
if ($sol['possui_excecao'] && $sol['excecao_id']) {
    $excecaoInfo = (new ExcecaoRepository())->buscarPorId((int)$sol['excecao_id']);
}

$historico = (new HistoricoRepository())->listarPorSolicitacao($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();
    $acao = inputPost('acao');
    $fluxo = new FluxoAprovacaoService();
    try {
        if ($acao === 'aprovar') {
            $fluxo->aprovar($id, Auth::id());
            flashMessage('success', 'Solicitação aprovada e encaminhada para lançamento no FLIT.');
        } elseif ($acao === 'reprovar') {
            $fluxo->reprovar($id, Auth::id(), inputPost('motivo_reprovacao', ''));
            flashMessage('success', 'Solicitação reprovada.');
        }
        redirect(url('aprovacoes_lista.php'));
    } catch (TransicaoInvalidaException $e) {
        flashMessage('danger', $e->getMessage());
        redirect(url('aprovacoes_ver.php?id=' . $id));
    }
}

view('aprovacoes/ver', compact('sol', 'detalhe', 'situacaoLimite', 'excecaoInfo', 'historico'));
