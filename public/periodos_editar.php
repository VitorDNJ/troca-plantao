<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\PeriodoRepository;
use App\Repositories\AuditoriaRepository;

Auth::requirePerfil(['ADMIN']);

$id = (int) inputGet('id', 0);
$repo = new PeriodoRepository();
$periodo = $repo->buscarPorId($id);

if (!$periodo) {
    http_response_code(404);
    die('Período não encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();

    $dados = [
        'nome' => inputPost('nome'),
        'data_inicial' => inputPost('data_inicial'),
        'data_final' => inputPost('data_final'),
        'limite_trocas' => (int) inputPost('limite_trocas'),
        'limite_passagens' => (int) inputPost('limite_passagens'),
        'status' => inputPost('status'),
        'regra' => inputPost('regra_troca_entre_periodos'),
        'observacao' => inputPost('observacao'),
    ];

    $repo->atualizar($id, $dados);
    (new AuditoriaRepository())->registrar(Auth::id(), 'EDITAR_PERIODO', 'periodos_controle', $id, $periodo, $dados);

    flashMessage('success', 'Período atualizado. Alterações de limite valem apenas a partir de agora — solicitações já registradas mantêm o cálculo histórico.');
    redirect(url('periodos_lista.php'));
}

$possuiVinculo = $repo->possuiSolicitacoesVinculadas($id);
view('periodos/editar', compact('periodo', 'possuiVinculo'));
