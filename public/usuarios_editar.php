<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\UsuarioRepository;
use App\Repositories\SetorRepository;
use App\Repositories\AuditoriaRepository;

Auth::requirePerfil(['ADMIN']);

$id = (int) inputGet('id', 0);
$usuarioRepo = new UsuarioRepository();
$usuario = $usuarioRepo->buscarPorId($id);

if (!$usuario) {
    http_response_code(404);
    die('Usuário não encontrado.');
}

$setorRepo = new SetorRepository();
$setores = $setorRepo->listarTodos(true);
$perfis = $usuarioRepo->listarPerfis();
$setoresVinculados = array_column($usuarioRepo->setoresDoCoordenador($id), 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();

    $dados = [
        'nome' => inputPost('nome'),
        'cpf' => preg_replace('/\D/', '', inputPost('cpf', '')),
        'email' => inputPost('email'),
        'setor_id' => (int) inputPost('setor_id'),
        'funcao' => inputPost('funcao'),
        'perfil_id' => (int) inputPost('perfil_id'),
        'status' => inputPost('status'),
    ];

    if ($usuarioRepo->matriculaOuCpfExiste($usuario['matricula'], $dados['cpf'], $id)) {
        flashMessage('danger', 'Já existe outro usuário com este CPF.');
        redirect(url('usuarios_editar.php?id=' . $id));
    }

    $usuarioRepo->atualizar($id, $dados);

    $usuarioRepo->desvincularTodosSetores($id);
    foreach ($_POST['setores_extra'] ?? [] as $sid) {
        $usuarioRepo->vincularSetor($id, (int)$sid);
    }

    (new AuditoriaRepository())->registrar(Auth::id(), 'EDITAR_USUARIO', 'usuarios', $id, $usuario, $dados);

    flashMessage('success', 'Usuário atualizado com sucesso.');
    redirect(url('usuarios_lista.php'));
}

view('usuarios/editar', compact('usuario', 'setores', 'perfis', 'setoresVinculados'));
