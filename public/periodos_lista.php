<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\PeriodoRepository;
use App\Repositories\AuditoriaRepository;

Auth::requirePerfil(['ADMIN']);

$repo = new PeriodoRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();

    $dados = [
        'nome' => inputPost('nome'),
        'data_inicial' => inputPost('data_inicial'),
        'data_final' => inputPost('data_final'),
        'limite_trocas' => (int) inputPost('limite_trocas', 2),
        'limite_passagens' => (int) inputPost('limite_passagens', 2),
        'status' => inputPost('status', 'FUTURO'),
        'regra' => inputPost('regra_troca_entre_periodos', 'SOMENTE_AUTORIZACAO'),
        'observacao' => inputPost('observacao'),
    ];

    if ($dados['data_final'] < $dados['data_inicial']) {
        flashMessage('danger', 'A data final não pode ser anterior à data inicial.');
        redirect(url('periodos_lista.php'));
    }

    $id = $repo->criar(array_merge($dados, ['criado_por' => Auth::id()]));
    (new AuditoriaRepository())->registrar(Auth::id(), 'CRIAR_PERIODO', 'periodos_controle', $id, null, $dados);

    flashMessage('success', 'Período criado com sucesso.');
    redirect(url('periodos_lista.php'));
}

$periodos = $repo->listarTodos();
view('periodos/lista', compact('periodos'));
