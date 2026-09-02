<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\NotificacaoRepository;

Auth::requireLogin();

$repo = new NotificacaoRepository();
$notificacoes = $repo->listarRecentes(Auth::id(), 50);
$repo->marcarTodasComoLidas(Auth::id());

view('notificacoes/lista', compact('notificacoes'));
