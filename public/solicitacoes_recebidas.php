<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\SolicitacaoRepository;

Auth::requirePerfil(['COLABORADOR']);

$repo = new SolicitacaoRepository();
$recebidas = $repo->listarRecebidas(Auth::id());

view('trocas/recebidas', compact('recebidas'));
