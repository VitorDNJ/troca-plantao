<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\SolicitacaoRepository;

Auth::requirePerfil(['COLABORADOR']);

$tipo = inputGet('tipo') ?: null;
$repo = new SolicitacaoRepository();
$solicitacoes = $repo->listarMinhas(Auth::id(), $tipo);

view('trocas/minhas', compact('solicitacoes', 'tipo'));
