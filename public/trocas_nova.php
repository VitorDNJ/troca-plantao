<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\UsuarioRepository;
use App\Repositories\PeriodoRepository;
use App\Services\SolicitacaoService;
use App\Services\LimiteExcedidoException;
use App\Services\RegraPeriodoException;

Auth::requirePerfil(['COLABORADOR']);

$usuarioRepo = new UsuarioRepository();
$solicitante = $usuarioRepo->buscarPorId(Auth::id());

$periodoRepo = new PeriodoRepository();
$periodoAtivo = $periodoRepo->periodoAtivo();

$limiteAtingido = false;
$mensagemErro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();

    $dados = [
        'meu_data' => inputPost('meu_data'),
        'meu_hora_inicial' => inputPost('meu_hora_inicial'),
        'meu_hora_final' => inputPost('meu_hora_final'),
        'meu_turno' => inputPost('meu_turno'),
        'outro_usuario_id' => (int) inputPost('outro_usuario_id'),
        'outro_data' => inputPost('outro_data'),
        'outro_hora_inicial' => inputPost('outro_hora_inicial'),
        'outro_hora_final' => inputPost('outro_hora_final'),
        'outro_turno' => inputPost('outro_turno'),
        'motivo' => inputPost('motivo'),
        'observacao' => inputPost('observacao'),
    ];

    $camposObrigatorios = ['meu_data','meu_hora_inicial','meu_hora_final','meu_turno','outro_usuario_id','outro_data','outro_hora_inicial','outro_hora_final','outro_turno'];
    $faltando = array_filter($camposObrigatorios, fn($c) => empty($dados[$c]));

    if (!empty($faltando) || $dados['outro_usuario_id'] === (int)Auth::id()) {
        flashMessage('danger', empty($faltando) ? 'Você não pode trocar plantão com você mesmo.' : 'Preencha todos os campos obrigatórios.');
        setOld($dados);
        redirect(url('trocas_nova.php'));
    }

    try {
        $resultado = (new SolicitacaoService())->criarTroca(Auth::id(), $dados);
        flashMessage('success', "Solicitação de troca {$resultado['codigo']} criada com sucesso! Aguardando aceite do outro colaborador.");
        redirect(url('trocas_ver.php?id=' . $resultado['id']));
    } catch (LimiteExcedidoException $e) {
        $limiteAtingido = true;
        setOld($dados);
    } catch (RegraPeriodoException $e) {
        $mensagemErro = $e->getMessage();
        setOld($dados);
    }
}

view('trocas/nova', compact('solicitante', 'periodoAtivo', 'limiteAtingido', 'mensagemErro'));
