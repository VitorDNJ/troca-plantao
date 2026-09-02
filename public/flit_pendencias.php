<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SetorRepository;
use App\Repositories\SolicitacaoRepository;
use App\Services\FluxoAprovacaoService;

Auth::requirePerfil(['COORDENADOR']);

$setorRepo = new SetorRepository();
$setorIds = $setorRepo->idsPorCoordenador(Auth::id());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();
    $idsArray = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];

    // Segurança: restringe o lote apenas às solicitações realmente pendentes
    // dentro dos setores sob responsabilidade deste coordenador (evita
    // manipulação do ID para lançar solicitações de outros setores).
    if (!empty($idsArray)) {
        $repoCheck = new SolicitacaoRepository();
        $permitidos = array_column($repoCheck->listarPendentesFlit($setorIds), 'id');
        $idsArray = array_values(array_intersect($idsArray, array_map('intval', $permitidos)));
    }


    if (empty($idsArray)) {
        flashMessage('danger', 'Selecione ao menos uma solicitação.');
    } else {
        $resultado = (new FluxoAprovacaoService())->marcarLoteLancadoFlit($idsArray, Auth::id());
        $qtdSucesso = count($resultado['sucesso']);
        $qtdFalha = count($resultado['falhas']);
        $tipo = $qtdFalha > 0 ? 'warning' : 'success';
        flashMessage($tipo, "{$qtdSucesso} solicitação(ões) marcada(s) como lançada(s) no FLIT." . ($qtdFalha > 0 ? " {$qtdFalha} não puderam ser processadas (já lançadas ou inválidas)." : ''));
    }
    redirect(url('flit_pendencias.php'));
}

$repo = new SolicitacaoRepository();
$pendentes = $repo->listarPendentesFlit($setorIds);

view('flit/pendencias', compact('pendentes'));
