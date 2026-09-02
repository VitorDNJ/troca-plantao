<?php

namespace App\Core;

use App\Repositories\UsuarioRepository;

class Auth
{
    public static function attempt(string $matricula, string $senha): array
    {
        $app = require __DIR__ . '/../../config/app.php';
        $repo = new UsuarioRepository();
        $usuario = $repo->buscarPorMatricula($matricula);

        if (!$usuario) {
            return ['ok' => false, 'erro' => 'Matrícula ou senha inválidos.'];
        }

        if ($usuario['status'] !== 'ATIVO') {
            return ['ok' => false, 'erro' => 'Usuário inativo. Procure o RH.'];
        }

        if (!empty($usuario['bloqueado_ate']) && strtotime($usuario['bloqueado_ate']) > time()) {
            return ['ok' => false, 'erro' => 'Conta temporariamente bloqueada por excesso de tentativas. Tente novamente mais tarde.'];
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            $tentativas = (int)$usuario['tentativas_login'] + 1;
            $bloqueadoAte = null;
            if ($tentativas >= $app['login_max_tentativas']) {
                $bloqueadoAte = date('Y-m-d H:i:s', time() + ($app['login_bloqueio_minutos'] * 60));
                $tentativas = 0;
            }
            $repo->registrarTentativaFalha($usuario['id'], $tentativas, $bloqueadoAte);
            return ['ok' => false, 'erro' => 'Matrícula ou senha inválidos.'];
        }

        // login OK: zera tentativas
        $repo->registrarTentativaFalha($usuario['id'], 0, null);

        Session::regenerate();
        Session::set('usuario_id', $usuario['id']);
        Session::set('usuario_nome', $usuario['nome']);
        Session::set('usuario_matricula', $usuario['matricula']);
        Session::set('usuario_perfil', $usuario['perfil_codigo']);
        Session::set('usuario_setor_id', $usuario['setor_id']);
        Session::set('usuario_setor_nome', $usuario['setor_nome']);
        Session::set('trocar_senha', (bool)$usuario['trocar_senha_primeiro_acesso']);

        return ['ok' => true, 'usuario' => $usuario];
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function checado(): bool
    {
        return Session::has('usuario_id');
    }

    public static function id(): ?int
    {
        return Session::get('usuario_id');
    }

    public static function nome(): ?string
    {
        return Session::get('usuario_nome');
    }

    public static function perfil(): ?string
    {
        return Session::get('usuario_perfil');
    }

    public static function setorId(): ?int
    {
        return Session::get('usuario_setor_id');
    }

    public static function isAdmin(): bool
    {
        return self::perfil() === 'ADMIN';
    }

    public static function isCoordenador(): bool
    {
        return self::perfil() === 'COORDENADOR';
    }

    public static function isColaborador(): bool
    {
        return self::perfil() === 'COLABORADOR';
    }

    public static function precisaTrocarSenha(): bool
    {
        return (bool) Session::get('trocar_senha', false);
    }

    public static function requireLogin(): void
    {
        if (!self::checado()) {
            redirect(url('login.php'));
        }
    }

    public static function requirePerfil(array $perfis): void
    {
        self::requireLogin();
        if (!in_array(self::perfil(), $perfis, true)) {
            http_response_code(403);
            die('Acesso negado: seu perfil não tem permissão para acessar esta página.');
        }
    }
}
