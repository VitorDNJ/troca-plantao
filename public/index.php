<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();

if (Auth::precisaTrocarSenha()) {
    redirect(url('trocar_senha.php'));
}

if (Auth::isAdmin()) {
    require __DIR__ . '/dashboard_admin.php';
} elseif (Auth::isCoordenador()) {
    require __DIR__ . '/dashboard_coordenador.php';
} else {
    require __DIR__ . '/dashboard_colaborador.php';
}
