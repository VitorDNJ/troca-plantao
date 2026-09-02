<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\UsuarioRepository;
use App\Repositories\AuditoriaRepository;

class AuthController
{
    public function login(): void
    {
        Csrf::verifyRequestOrFail();

        $matricula = inputPost('matricula', '');
        $senha = inputPost('senha', '');

        if ($matricula === '' || $senha === '') {
            flashMessage('danger', 'Informe matrícula e senha.');
            redirect(url('login.php'));
        }

        $resultado = Auth::attempt($matricula, $senha);

        if (!$resultado['ok']) {
            (new AuditoriaRepository())->registrar(null, 'LOGIN_FALHA', 'usuarios', null, null, ['matricula' => $matricula]);
            flashMessage('danger', $resultado['erro']);
            redirect(url('login.php'));
        }

        (new AuditoriaRepository())->registrar($resultado['usuario']['id'], 'LOGIN_SUCESSO', 'usuarios', $resultado['usuario']['id']);

        if (Auth::precisaTrocarSenha()) {
            redirect(url('trocar_senha.php'));
        }

        redirect(url('index.php'));
    }

    public function logout(): void
    {
        if (Auth::checado()) {
            (new AuditoriaRepository())->registrar(Auth::id(), 'LOGOUT', 'usuarios', Auth::id());
        }
        Auth::logout();
        redirect(url('login.php'));
    }

    public function trocarSenha(): void
    {
        Auth::requireLogin();
        Csrf::verifyRequestOrFail();

        $atual = inputPost('senha_atual', '');
        $nova = inputPost('senha_nova', '');
        $confirmacao = inputPost('senha_confirmacao', '');

        $repo = new UsuarioRepository();
        $usuario = $repo->buscarPorId(Auth::id());

        if (!password_verify($atual, $usuario['senha_hash'])) {
            flashMessage('danger', 'Senha atual incorreta.');
            redirect(url('trocar_senha.php'));
        }

        if (strlen($nova) < 8) {
            flashMessage('danger', 'A nova senha deve ter pelo menos 8 caracteres.');
            redirect(url('trocar_senha.php'));
        }

        if ($nova !== $confirmacao) {
            flashMessage('danger', 'A confirmação de senha não confere.');
            redirect(url('trocar_senha.php'));
        }

        $repo->atualizarSenha(Auth::id(), password_hash($nova, PASSWORD_DEFAULT), false);
        \App\Core\Session::set('trocar_senha', false);
        (new AuditoriaRepository())->registrar(Auth::id(), 'TROCAR_SENHA', 'usuarios', Auth::id());

        flashMessage('success', 'Senha alterada com sucesso.');
        redirect(url('index.php'));
    }
}
