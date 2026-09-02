<h4 class="mb-4">Painel Administrativo</h4>

<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total de trocas', $totais['total_trocas'] ?? 0, 'primary'],
    ['Total de passagens', $totais['total_passagens'] ?? 0, 'primary'],
    ['Pendentes', $totais['pendentes'] ?? 0, 'warning'],
    ['Aprovadas', $totais['aprovadas'] ?? 0, 'success'],
    ['Recusadas/Reprovadas', $totais['recusadas'] ?? 0, 'danger'],
    ['Com exceção', $totais['com_excecao'] ?? 0, 'info'],
    ['Pendentes FLIT', $totais['pendentes_flit'] ?? 0, 'warning'],
    ['Lançadas FLIT', $totais['lancadas_flit'] ?? 0, 'success'],
  ];
  foreach ($cards as [$titulo, $valor, $cor]): ?>
    <div class="col-md-3">
      <div class="card card-dashboard p-3 text-center">
        <div class="text-muted small"><?= h($titulo) ?></div>
        <div class="valor text-<?= $cor ?>"><?= (int)$valor ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card card-dashboard p-3">
      <h6>Solicitações por setor</h6>
      <table class="table table-sm tabela-compacta">
        <?php foreach ($porSetor as $r): ?>
          <tr><td><?= h($r['setor']) ?></td><td class="text-end"><?= (int)$r['total'] ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-dashboard p-3">
      <h6>Solicitações por período</h6>
      <table class="table table-sm tabela-compacta">
        <?php foreach ($porPeriodo as $r): ?>
          <tr><td><?= h($r['periodo']) ?></td><td class="text-end"><?= (int)$r['total'] ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-dashboard p-3">
      <h6>Colaboradores com mais solicitações</h6>
      <table class="table table-sm tabela-compacta">
        <?php foreach ($topColaboradores as $r): ?>
          <tr><td><?= h($r['nome']) ?></td><td class="text-end"><?= (int)$r['total'] ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>

<div class="mt-4">
  <a href="<?= url('usuarios_lista.php') ?>" class="btn btn-outline-primary me-2">Gerenciar usuários</a>
  <a href="<?= url('setores_lista.php') ?>" class="btn btn-outline-primary me-2">Gerenciar setores</a>
  <a href="<?= url('periodos_lista.php') ?>" class="btn btn-outline-primary me-2">Gerenciar períodos</a>
  <a href="<?= url('auditoria_lista.php') ?>" class="btn btn-outline-secondary">Ver auditoria</a>
</div>
