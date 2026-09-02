<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\PeriodoRepository;
use App\Repositories\SetorRepository;
use App\Repositories\TrocaRepository;
use App\Repositories\PassagemRepository;

Auth::requirePerfil(['ADMIN', 'COORDENADOR']);

$periodoRepo = new PeriodoRepository();
$setorRepo = new SetorRepository();
$periodos = $periodoRepo->listarTodos();
$setores = $setorRepo->listarTodos();

$filtros = [
    'periodo_id' => inputGet('periodo_id') ?: null,
    'setor_id' => inputGet('setor_id') ?: null,
    'usuario_id' => inputGet('usuario_id') ?: null,
    'tipo' => inputGet('tipo') ?: null,
    'status' => inputGet('status') ?: null,
    'flit_status' => inputGet('flit_status', ''),
    'excecao' => inputGet('excecao', ''),
];

// Coordenador só vê os setores sob sua responsabilidade
if (Auth::isCoordenador()) {
    $filtros['setor_ids_permitidos'] = $setorRepo->idsPorCoordenador(Auth::id());
}

$repo = new SolicitacaoRepository();
$resultados = $repo->filtrar($filtros);

$trocaRepo = new TrocaRepository();
$passagemRepo = new PassagemRepository();

$trocas = [];
$passagens = [];
foreach ($resultados as $r) {
    if ($r['tipo'] === 'TROCA') {
        $detalhe = $trocaRepo->buscarPorSolicitacao($r['id']);
        $trocas[] = array_merge($r, ['detalhe' => $detalhe]);
    } else {
        $detalhe = $passagemRepo->buscarPorSolicitacao($r['id']);
        $passagens[] = array_merge($r, ['detalhe' => $detalhe]);
    }
}

view('relatorios/flit', compact('periodos', 'setores', 'filtros', 'trocas', 'passagens'));
