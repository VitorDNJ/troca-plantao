<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\UsuarioRepository;
use App\Repositories\AuditoriaRepository;

Auth::requirePerfil(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('usuarios_lista.php'));
}
Csrf::verifyRequestOrFail();

$id = (int) inputPost('id');
$acao = inputPost('acao');
$repo = new UsuarioRepository();
$auditoria = new AuditoriaRepository();

if ($acao === 'alternar_status') {
    $usuario = $repo->buscarPorId($id);
    if ($usuario) {
        $novo = $usuario['status'] === 'ATIVO' ? 'INATIVO' : 'ATIVO';
        $repo->alternarStatus($id, $novo);
        $auditoria->registrar(Auth::id(), 'ALTERAR_STATUS_USUARIO', 'usuarios', $id, ['status' => $usuario['status']], ['status' => $novo]);
        $rotulo = $novo === 'ATIVO' ? 'ativado' : 'desativado';
        flashMessage('success', "Usuário {$rotulo} com sucesso.");
    }
} elseif ($acao === 'senha_temporaria') {
    $novaSenha = substr(bin2hex(random_bytes(5)), 0, 8);
    $repo->atualizarSenha($id, password_hash($novaSenha, PASSWORD_DEFAULT), true);
    $auditoria->registrar(Auth::id(), 'GERAR_SENHA_TEMPORARIA', 'usuarios', $id);
    flashMessage('success', "Senha temporária gerada: {$novaSenha} (informe ao colaborador; ele deverá trocá-la no próximo acesso).");
}

redirect(url('usuarios_lista.php'));
