<h4 class="mb-4">Solicitações Recebidas</h4>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>De</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($recebidas as $s): ?>
      <tr>
        <td><?= h($s['codigo']) ?></td>
        <td><?= h($s['origem']) ?></td>
        <td><?= h($s['solicitante_nome']) ?></td>
        <td><?= statusBadge($s['status']) ?></td>
        <td><a href="<?= url(($s['origem']==='TROCA'?'trocas_ver.php':'passagens_ver.php').'?id='.$s['id']) ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($recebidas)): ?>
        <tr><td colspan="5" class="text-center text-muted">Nenhuma solicitação recebida.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
