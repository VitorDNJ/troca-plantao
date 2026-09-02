<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\ExcecaoRepository;
use App\Repositories\PeriodoRepository;
use App\Repositories\SetorRepository;

Auth::requirePerfil(['ADMIN', 'COORDENADOR']);

$periodoRepo = new PeriodoRepository();
$setorRepo = new SetorRepository();
$periodos = $periodoRepo->listarTodos();
$setores = $setorRepo->listarTodos();

$filtros = [
    'periodo_id' => inputGet('periodo_id') ?: null,
    'setor_id' => inputGet('setor_id') ?: null,
];

$repo = new ExcecaoRepository();
$excecoes = $repo->relatorio($filtros);

// Coordenador só vê exceções dos setores sob sua responsabilidade
if (Auth::isCoordenador()) {
    $setorIds = $setorRepo->idsPorCoordenador(Auth::id());
    $excecoes = array_values(array_filter($excecoes, fn($e) => in_array((int)$e['setor_id'], $setorIds, true)));
}

view('relatorios/excecoes', compact('periodos', 'setores', 'filtros', 'excecoes'));
