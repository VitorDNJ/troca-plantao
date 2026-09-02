<h4 class="mb-4">Auditoria do Sistema</h4>

<div class="card card-dashboard p-3 mb-4">
  <form method="get" class="row g-2">
    <div class="col-md-4">
      <select name="entidade" class="form-select form-select-sm">
        <option value="">Todas as entidades</option>
        <?php foreach ($entidades as $e): ?>
          <option value="<?= $e ?>" <?= $filtros['entidade']===$e?'selected':'' ?>><?= h($e) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <select name="usuario_id" class="form-select form-select-sm">
        <option value="">Todos os usuários</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $filtros['usuario_id']==$u['id']?'selected':'' ?>><?= h($u['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4"><button type="submit" class="btn btn-primary btn-sm">Filtrar</button></div>
  </form>
</div>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table table-sm tabela-compacta">
    <thead><tr><th>Data/Hora</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>ID</th><th>IP</th></tr></thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
      <tr>
        <td><?= formatarDataHora($l['criado_em']) ?></td>
        <td><?= h($l['usuario_nome'] ?? 'Sistema') ?></td>
        <td><?= h($l['acao']) ?></td>
        <td><?= h($l['entidade']) ?></td>
        <td><?= h((string)$l['entidade_id']) ?></td>
        <td><?= h($l['ip']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($logs)): ?><tr><td colspan="6" class="text-center text-muted">Nenhum registro encontrado.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
