<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\PeriodoRepository;
use App\Services\LimiteService;
use App\Repositories\SolicitacaoRepository;

Auth::requireLogin();

$periodoRepo = new PeriodoRepository();
$periodo = $periodoRepo->periodoAtivo();
$limiteService = new LimiteService();
$resumo = $periodo ? $limiteService->resumoUsuarioPeriodo(Auth::id(), (int)$periodo['id']) : null;

$solicitacaoRepo = new SolicitacaoRepository();
$minhas = $solicitacaoRepo->listarMinhas(Auth::id());
$recebidas = array_filter($solicitacaoRepo->listarRecebidas(Auth::id()), fn($s) => $s['status'] === 'PENDENTE_ACEITE');

view('dashboard/colaborador', compact('periodo', 'resumo', 'minhas', 'recebidas'));
