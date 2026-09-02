<h4 class="mb-4">Solicitações Aguardando Aprovação</h4>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>Solicitante</th><th>Matrícula</th><th>Setor</th><th>Criado em</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($aguardando as $s): ?>
      <tr>
        <td><?= h($s['codigo']) ?></td>
        <td><?= h($s['tipo']) ?></td>
        <td><?= h($s['solicitante_nome']) ?></td>
        <td><?= h($s['solicitante_matricula']) ?></td>
        <td><?= h($s['setor_nome']) ?></td>
        <td><?= formatarDataHora($s['criado_em']) ?></td>
        <td><a href="<?= url('aprovacoes_ver.php?id=' . $s['id']) ?>" class="btn btn-sm btn-primary">Analisar</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($aguardando)): ?>
        <tr><td colspan="7" class="text-center text-muted">Nenhuma pendência de aprovação.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
