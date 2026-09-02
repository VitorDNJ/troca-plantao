<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\UsuarioRepository;
use App\Repositories\SetorRepository;
use App\Repositories\AuditoriaRepository;

Auth::requirePerfil(['ADMIN']);

$usuarioRepo = new UsuarioRepository();
$setorRepo = new SetorRepository();
$setores = $setorRepo->listarTodos(true);
$perfis = $usuarioRepo->listarPerfis();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();

    $dados = [
        'matricula' => inputPost('matricula'),
        'nome' => inputPost('nome'),
        'cpf' => preg_replace('/\D/', '', inputPost('cpf', '')),
        'email' => inputPost('email'),
        'setor_id' => (int) inputPost('setor_id'),
        'funcao' => inputPost('funcao'),
        'perfil_id' => (int) inputPost('perfil_id'),
        'status' => 'ATIVO',
    ];

    $obrigatorios = ['matricula','nome','cpf','email','setor_id','funcao','perfil_id'];
    $faltando = array_filter($obrigatorios, fn($c) => empty($dados[$c]));

    if (!empty($faltando)) {
        flashMessage('danger', 'Preencha todos os campos obrigatórios.');
        setOld($dados);
        redirect(url('usuarios_novo.php'));
    }

    if ($usuarioRepo->matriculaOuCpfExiste($dados['matricula'], $dados['cpf'])) {
        flashMessage('danger', 'Já existe um usuário com esta matrícula ou CPF.');
        setOld($dados);
        redirect(url('usuarios_novo.php'));
    }

    $senhaTemporaria = substr(bin2hex(random_bytes(5)), 0, 8);
    $dados['senha_hash'] = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

    $id = $usuarioRepo->criar($dados);

    // vínculo extra de setores para coordenadores
    $setoresExtras = $_POST['setores_extra'] ?? [];
    foreach ($setoresExtras as $sid) {
        $usuarioRepo->vincularSetor($id, (int)$sid);
    }

    (new AuditoriaRepository())->registrar(Auth::id(), 'CRIAR_USUARIO', 'usuarios', $id, null, ['matricula' => $dados['matricula']]);

    flashMessage('success', "Usuário criado com sucesso! Senha temporária: {$senhaTemporaria} (deverá ser trocada no primeiro acesso).");
    redirect(url('usuarios_lista.php'));
}

view('usuarios/novo', compact('setores', 'perfis'));
