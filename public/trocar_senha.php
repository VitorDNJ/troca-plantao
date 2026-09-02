<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Controllers\AuthController;

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AuthController())->trocarSenha();
}

view('auth/trocar_senha', []);
