<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\UsuarioRepository;
use App\Services\SolicitacaoService;
use App\Services\LimiteExcedidoException;
use App\Services\RegraPeriodoException;

Auth::requirePerfil(['COLABORADOR']);

$usuarioRepo = new UsuarioRepository();
$solicitante = $usuarioRepo->buscarPorId(Auth::id());

$limiteAtingido = false;
$mensagemErro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequestOrFail();

    $dados = [
        'data' => inputPost('data'),
        'hora_inicial' => inputPost('hora_inicial'),
        'hora_final' => inputPost('hora_final'),
        'turno' => inputPost('turno'),
        'quem_recebeu_id' => (int) inputPost('quem_recebeu_id'),
        'motivo' => inputPost('motivo'),
        'observacao' => inputPost('observacao'),
    ];

    $obrigatorios = ['data','hora_inicial','hora_final','turno','quem_recebeu_id'];
    $faltando = array_filter($obrigatorios, fn($c) => empty($dados[$c]));

    if (!empty($faltando) || $dados['quem_recebeu_id'] === (int)Auth::id()) {
        flashMessage('danger', empty($faltando) ? 'Você não pode passar o plantão para você mesmo.' : 'Preencha todos os campos obrigatórios.');
        setOld($dados);
        redirect(url('passagens_nova.php'));
    }

    try {
        $resultado = (new SolicitacaoService())->criarPassagem(Auth::id(), $dados);
        flashMessage('success', "Solicitação de passagem {$resultado['codigo']} criada com sucesso! Aguardando aceite do colaborador.");
        redirect(url('passagens_ver.php?id=' . $resultado['id']));
    } catch (LimiteExcedidoException $e) {
        $limiteAtingido = true;
        setOld($dados);
    } catch (RegraPeriodoException $e) {
        $mensagemErro = $e->getMessage();
        setOld($dados);
    }
}

view('passagens/nova', compact('solicitante', 'limiteAtingido', 'mensagemErro'));
