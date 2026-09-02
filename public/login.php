<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Controllers\AuthController;

if (Auth::checado()) {
    redirect(url('index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AuthController())->login();
}

$msg = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Troca e Passagem de Plantão</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/estilo.css') ?>">
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="card card-dashboard p-4">
        <h4 class="text-center mb-1">🩺 Troca &amp; Passagem de Plantão</h4>
        <p class="text-center text-muted mb-4">Acesse com sua matrícula</p>

        <?php if ($msg): ?>
          <div class="alert alert-<?= h($msg['tipo']) ?>"><?= h($msg['mensagem']) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('login.php') ?>">
          <?= Csrf::field() ?>
          <div class="mb-3">
            <label class="form-label">Matrícula</label>
            <input type="text" class="form-control" name="matricula" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" class="form-control" name="senha" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
