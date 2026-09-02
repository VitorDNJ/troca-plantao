<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\PeriodoRepository;
use App\Services\ExcecaoService;

Auth::requirePerfil(['COLABORADOR']);

$tipo = inputGet('tipo') === 'PASSAGEM' ? 'PASSAGEM' : 'TROCA';

$periodoRepo = new PeriodoRepository();
$periodo = $periodoRepo->periodoAtivo();

if (!$periodo) {
    flashMessage('danger', 'Nenhum período ativo encontrado.');
    redirect(url('index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();
    $tipo = inputPost('tipo') === 'PASSAGEM' ? 'PASSAGEM' : 'TROCA';
    $justificativa = inputPost('justificativa', '');

    if (trim($justificativa) === '') {
        flashMessage('danger', 'A justificativa é obrigatória.');
        redirect(url('excecoes_solicitar.php?tipo=' . $tipo));
    }

    $usuario = (new \App\Repositories\UsuarioRepository())->buscarPorId(Auth::id());

    $resultado = (new ExcecaoService())->solicitar(
        Auth::id(),
        (int)$usuario['setor_id'],
        (int)$periodo['id'],
        $tipo,
        $justificativa
    );

    flashMessage('success', "Pedido de exceção {$resultado['codigo']} enviado ao coordenador.");
    redirect(url('index.php'));
}

view('excecoes/solicitar', compact('tipo', 'periodo'));
