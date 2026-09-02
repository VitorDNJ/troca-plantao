<?php
use App\Core\Auth;
use App\Helpers\StatusSolicitacao;
$notifRepo = Auth::checado() ? new \App\Repositories\NotificacaoRepository() : null;
$naoLidas = $notifRepo ? $notifRepo->contarNaoLidas(Auth::id()) : 0;
$msg = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema de Troca e Passagem de Plantão</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/estilo.css') ?>">
</head>
<body>
<?php if (Auth::checado()): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= url('index.php') ?>">🩺 Troca &amp; Passagem de Plantão</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= url('index.php') ?>">Início</a></li>

        <?php if (Auth::isColaborador()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('trocas_nova.php') ?>">Solicitar Troca</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('passagens_nova.php') ?>">Solicitar Passagem</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('minhas_solicitacoes.php') ?>">Minhas Solicitações</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('solicitacoes_recebidas.php') ?>">Recebidas</a></li>
        <?php endif; ?>

        <?php if (Auth::isCoordenador()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('aprovacoes_lista.php') ?>">Aprovações</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('excecoes_lista.php') ?>">Exceções</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('flit_pendencias.php') ?>">Pendências FLIT</a></li>
        <?php endif; ?>

        <?php if (Auth::isAdmin() || Auth::isCoordenador()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Relatórios</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= url('relatorios_flit.php') ?>">Relatório FLIT (trocas/passagens)</a></li>
            <li><a class="dropdown-item" href="<?= url('relatorios_periodo.php') ?>">Relatório por período</a></li>
            <li><a class="dropdown-item" href="<?= url('relatorios_individual.php') ?>">Relatório individual</a></li>
            <li><a class="dropdown-item" href="<?= url('relatorios_excecoes.php') ?>">Relatório de exceções</a></li>
          </ul>
        </li>
        <?php endif; ?>

        <?php if (Auth::isAdmin()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Administração</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= url('usuarios_lista.php') ?>">Usuários</a></li>
            <li><a class="dropdown-item" href="<?= url('setores_lista.php') ?>">Setores</a></li>
            <li><a class="dropdown-item" href="<?= url('periodos_lista.php') ?>">Períodos</a></li>
            <li><a class="dropdown-item" href="<?= url('auditoria_lista.php') ?>">Auditoria</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="<?= url('notificacoes.php') ?>">🔔 <?= $naoLidas > 0 ? "<span class='badge bg-danger'>{$naoLidas}</span>" : '' ?></a></li>
        <li class="nav-item"><span class="nav-link text-light-emphasis"><?= h(Auth::nome()) ?> <small>(<?= h(Auth::perfil()) ?>)</small></span></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('trocar_senha.php') ?>">Alterar senha</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('logout.php') ?>">Sair</a></li>
      </ul>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="container-fluid py-4">
  <?php if ($msg): ?>
    <div class="alert alert-<?= h($msg['tipo']) ?> alert-dismissible fade show" role="alert">
      <?= h($msg['mensagem']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
