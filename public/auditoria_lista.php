<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\AuditoriaRepository;
use App\Repositories\UsuarioRepository;

Auth::requirePerfil(['ADMIN']);

$filtros = [
    'entidade' => inputGet('entidade') ?: null,
    'usuario_id' => inputGet('usuario_id') ?: null,
];

$repo = new AuditoriaRepository();
$logs = $repo->listar($filtros, 300);

$usuarioRepo = new UsuarioRepository();
$usuarios = $usuarioRepo->listarTodos();

$entidades = ['usuarios', 'setores', 'periodos_controle', 'solicitacoes', 'excecoes_limite'];

view('auditoria/lista', compact('logs', 'usuarios', 'entidades', 'filtros'));
