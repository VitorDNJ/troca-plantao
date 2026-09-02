<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\UsuarioRepository;
use App\Repositories\SetorRepository;

Auth::requirePerfil(['ADMIN']);

$setorRepo = new SetorRepository();
$setores = $setorRepo->listarTodos();

$usuarioRepo = new UsuarioRepository();
$busca = inputGet('busca', '');
$setorId = inputGet('setor_id') ?: null;
$usuarios = $usuarioRepo->listarTodos($setorId ? (int)$setorId : null, $busca ?: null);

view('usuarios/lista', compact('usuarios', 'setores', 'busca', 'setorId'));
