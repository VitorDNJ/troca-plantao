<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SetorRepository;
use App\Repositories\ExcecaoRepository;
use App\Services\ExcecaoService;

Auth::requirePerfil(['COORDENADOR']);

$setorRepo = new SetorRepository();
$setorIds = $setorRepo->idsPorCoordenador(Auth::id());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();
    $id = (int) inputPost('id');
    $acao = inputPost('acao');

    $excecaoRepo = new ExcecaoRepository();
    $exc = $excecaoRepo->buscarPorId($id);
    if (!$exc || !in_array((int)$exc['setor_id'], $setorIds, true)) {
        http_response_code(403);
        die('Você não tem permissão para processar esta exceção.');
    }

    $service = new ExcecaoService();
    try {
        if ($acao === 'autorizar') {
            $service->autorizar($id, Auth::id());
            flashMessage('success', 'Exceção autorizada.');
        } elseif ($acao === 'negar') {
            $service->negar($id, Auth::id(), inputPost('motivo_negativa', ''));
            flashMessage('success', 'Exceção negada.');
        }
    } catch (\Throwable $e) {
        flashMessage('danger', $e->getMessage());
    }
    redirect(url('excecoes_lista.php'));
}

$excecaoRepo = new ExcecaoRepository();
$pendentes = $excecaoRepo->listarPendentesPorSetores($setorIds);

view('excecoes/lista', compact('pendentes'));
