<h4 class="mb-4">Minhas Solicitações</h4>

<div class="mb-3">
  <a href="<?= url('minhas_solicitacoes.php') ?>" class="btn btn-sm <?= !$tipo?'btn-primary':'btn-outline-primary' ?>">Todas</a>
  <a href="<?= url('minhas_solicitacoes.php?tipo=TROCA') ?>" class="btn btn-sm <?= $tipo==='TROCA'?'btn-primary':'btn-outline-primary' ?>">Trocas</a>
  <a href="<?= url('minhas_solicitacoes.php?tipo=PASSAGEM') ?>" class="btn btn-sm <?= $tipo==='PASSAGEM'?'btn-primary':'btn-outline-primary' ?>">Passagens</a>
</div>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>Período</th><th>Status</th><th>FLIT</th><th>Criado em</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($solicitacoes as $s): ?>
      <tr>
        <td><?= h($s['codigo']) ?></td>
        <td><?= h($s['tipo']) ?></td>
        <td><?= h($s['periodo_nome']) ?></td>
        <td><?= statusBadge($s['status']) ?> <?= $s['possui_excecao'] ? '<span class="badge bg-info">Exceção</span>' : '' ?></td>
        <td><?= $s['flit_status'] === 'LANCADA_FLIT' ? '<span class="badge bg-success">Lançada</span>' : ($s['flit_status'] === 'PENDENTE_FLIT' ? '<span class="badge bg-warning text-dark">Pendente</span>' : '-') ?></td>
        <td><?= formatarDataHora($s['criado_em']) ?></td>
        <td><a href="<?= url(($s['tipo']==='TROCA'?'trocas_ver.php':'passagens_ver.php').'?id='.$s['id']) ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($solicitacoes)): ?>
        <tr><td colspan="7" class="text-center text-muted">Nenhuma solicitação encontrada.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
