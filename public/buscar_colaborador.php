<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\UsuarioRepository;

Auth::requireLogin();

$termo = inputGet('q', '');
$excluir = inputGet('excluir', null);

if (strlen($termo) < 2) {
    jsonResponse(['resultados' => []]);
}

$repo = new UsuarioRepository();
$resultados = $repo->pesquisar($termo, $excluir ? (int)$excluir : null);

jsonResponse(['resultados' => $resultados]);
