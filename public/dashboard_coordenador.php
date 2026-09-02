<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\SetorRepository;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\ExcecaoRepository;
use App\Repositories\PeriodoRepository;

Auth::requirePerfil(['COORDENADOR']);

$setorRepo = new SetorRepository();
$setorIds = $setorRepo->idsPorCoordenador(Auth::id());

$solicitacaoRepo = new SolicitacaoRepository();
$contadores = $solicitacaoRepo->contadoresDashboard($setorIds);
$aguardando = $solicitacaoRepo->listarAguardandoCoordenador($setorIds);
$pendentesFlit = $solicitacaoRepo->listarPendentesFlit($setorIds);

$excecaoRepo = new ExcecaoRepository();
$excecoesPendentes = $excecaoRepo->listarPendentesPorSetores($setorIds);

$periodoRepo = new PeriodoRepository();
$periodo = $periodoRepo->periodoAtivo();

view('dashboard/coordenador', compact('contadores', 'aguardando', 'pendentesFlit', 'excecoesPendentes', 'periodo'));
