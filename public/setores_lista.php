<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\SetorRepository;
use App\Repositories\AuditoriaRepository;

Auth::requirePerfil(['ADMIN']);

$repo = new SetorRepository();
$auditoria = new AuditoriaRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();
    $acao = inputPost('acao');

    if ($acao === 'criar') {
        $nome = inputPost('nome');
        if (trim($nome) !== '') {
            $id = $repo->criar($nome);
            $auditoria->registrar(Auth::id(), 'CRIAR_SETOR', 'setores', $id, null, ['nome' => $nome]);
            flashMessage('success', 'Setor criado com sucesso.');
        }
    } elseif ($acao === 'editar') {
        $id = (int) inputPost('id');
        $nome = inputPost('nome');
        $repo->atualizar($id, $nome);
        $auditoria->registrar(Auth::id(), 'EDITAR_SETOR', 'setores', $id, null, ['nome' => $nome]);
        flashMessage('success', 'Setor atualizado com sucesso.');
    } elseif ($acao === 'alternar_status') {
        $id = (int) inputPost('id');
        $setor = $repo->buscarPorId($id);
        if ($setor) {
            $novo = $setor['status'] === 'ATIVO' ? 'INATIVO' : 'ATIVO';
            $repo->alternarStatus($id, $novo);
            $auditoria->registrar(Auth::id(), 'ALTERAR_STATUS_SETOR', 'setores', $id, ['status' => $setor['status']], ['status' => $novo]);
            flashMessage('success', 'Status do setor atualizado.');
        }
    }
    redirect(url('setores_lista.php'));
}

$setores = $repo->listarTodos();
view('setores/lista', compact('setores'));
