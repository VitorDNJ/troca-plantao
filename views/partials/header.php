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
<div class="app-shell">

  <!-- Barra superior somente em telas pequenas (abre o menu lateral) -->
  <nav class="navbar navbar-dark bg-dark d-lg-none">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <a class="navbar-brand ms-2" href="<?= url('index.php') ?>">🩺 Troca &amp; Passagem</a>
    </div>
  </nav>

  <!-- Menu lateral esquerdo (estático em telas grandes, offcanvas nas pequenas) -->
  <aside class="app-sidebar offcanvas-lg offcanvas-start bg-dark text-light" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header border-bottom border-secondary">
      <a class="navbar-brand text-light mb-0" id="sidebarMenuLabel" href="<?= url('index.php') ?>">🩺 Troca &amp; Passagem de Plantão</a>
      <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Fechar"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
      <ul class="nav flex-column p-3 gap-1">
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
          <li class="sidebar-heading">Relatórios</li>
          <li class="nav-item"><a class="nav-link" href="<?= url('relatorios_flit.php') ?>">Relatório FLIT</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('relatorios_periodo.php') ?>">Por período</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('relatorios_individual.php') ?>">Individual</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('relatorios_excecoes.php') ?>">De exceções</a></li>
        <?php endif; ?>

        <?php if (Auth::isAdmin()): ?>
          <li class="sidebar-heading">Administração</li>
          <li class="nav-item"><a class="nav-link" href="<?= url('usuarios_lista.php') ?>">Usuários</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('setores_lista.php') ?>">Setores</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('periodos_lista.php') ?>">Períodos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('auditoria_lista.php') ?>">Auditoria</a></li>
        <?php endif; ?>
      </ul>

      <div class="mt-auto border-top border-secondary p-3">
        <a class="nav-link px-0" href="<?= url('notificacoes.php') ?>">🔔 Notificações
          <?= $naoLidas > 0 ? "<span class='badge bg-danger ms-1'>{$naoLidas}</span>" : '' ?>
        </a>
        <div class="small text-light-emphasis mt-2"><?= h(Auth::nome()) ?> <span class="text-secondary">(<?= h(Auth::perfil()) ?>)</span></div>
        <a class="nav-link px-0" href="<?= url('trocar_senha.php') ?>">Alterar senha</a>
        <a class="nav-link px-0" href="<?= url('logout.php') ?>">Sair</a>
      </div>
    </div>
  </aside>

  <div class="app-main flex-grow-1">
<?php endif; ?>
<main class="container-fluid py-4">
  <?php if ($msg): ?>
    <div class="alert alert-<?= h($msg['tipo']) ?> alert-dismissible fade show" role="alert">
      <?= h($msg['mensagem']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
