<h4 class="mb-4">Relatório de Exceções Autorizadas</h4>

<div class="card card-dashboard p-3 mb-4">
  <form method="get" class="row g-2">
    <div class="col-md-4">
      <select name="periodo_id" class="form-select form-select-sm">
        <option value="">Todos os períodos</option>
        <?php foreach ($periodos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $filtros['periodo_id']==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <select name="setor_id" class="form-select form-select-sm">
        <option value="">Todos os setores</option>
        <?php foreach ($setores as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $filtros['setor_id']==$s['id']?'selected':'' ?>><?= h($s['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4"><button type="submit" class="btn btn-primary btn-sm">Filtrar</button></div>
  </form>
</div>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table table-sm tabela-compacta">
    <thead><tr><th>Código</th><th>Colaborador</th><th>Matrícula</th><th>Setor</th><th>Período</th><th>Tipo</th><th>Justificativa</th><th>Autorizado por</th><th>Data</th></tr></thead>
    <tbody>
      <?php foreach ($excecoes as $e): ?>
      <tr>
        <td><?= h($e['codigo']) ?></td>
        <td><?= h($e['usuario_nome']) ?></td>
        <td><?= h($e['usuario_matricula']) ?></td>
        <td><?= h($e['setor_nome']) ?></td>
        <td><?= h($e['periodo_nome']) ?></td>
        <td><?= h($e['tipo']) ?></td>
        <td><?= h($e['justificativa']) ?></td>
        <td><?= h($e['autorizador_nome'] ?? '-') ?></td>
        <td><?= formatarDataHora($e['autorizado_em']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($excecoes)): ?><tr><td colspan="9" class="text-center text-muted">Nenhuma exceção autorizada encontrada.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
