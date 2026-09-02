<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\SetorRepository;
use App\Repositories\SolicitacaoRepository;

Auth::requirePerfil(['COORDENADOR']);

$setorRepo = new SetorRepository();
$setorIds = $setorRepo->idsPorCoordenador(Auth::id());

$repo = new SolicitacaoRepository();
$aguardando = $repo->listarAguardandoCoordenador($setorIds);

view('aprovacoes/lista', compact('aguardando'));
