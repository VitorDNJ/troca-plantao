<h4 class="mb-4">Relatório por Período</h4>

<div class="card card-dashboard p-3 mb-4">
  <form method="get" class="row g-2">
    <div class="col-md-4">
      <select name="periodo_id" class="form-select" onchange="this.form.submit()">
        <?php foreach ($periodos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($periodo && $periodo['id']==$p['id'])?'selected':'' ?>><?= h($p['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<?php if (!$periodo): ?>
  <div class="alert alert-warning">Nenhum período selecionado.</div>
<?php else: ?>
<div class="card card-dashboard p-3">
  <h6><?= h($periodo['nome']) ?> (<?= formatarData($periodo['data_inicial']) ?> a <?= formatarData($periodo['data_final']) ?>)</h6>
  <div class="table-responsive">
  <table class="table table-sm tabela-compacta">
    <thead><tr><th>Colaborador</th><th>Trocas</th><th>Limite</th><th>Passou</th><th>Limite</th><th>Recebeu</th><th>Extra</th><th>Pendente FLIT</th></tr></thead>
    <tbody>
      <?php foreach ($linhas as $l): ?>
      <tr>
        <td><?= h($l['usuario']['nome']) ?> <?= ($l['troca']['em_excecao'] || $l['passagem']['em_excecao']) ? '<span class="badge bg-info ms-1">Exceção autorizada</span>' : '' ?></td>
        <td><?= $l['troca']['utilizadas'] ?>/<?= $l['troca']['limite_padrao'] ?></td>
        <td><?= $l['troca']['limite_padrao'] ?></td>
        <td><?= $l['passou'] ?>/<?= $l['passagem']['limite_padrao'] ?></td>
        <td><?= $l['passagem']['limite_padrao'] ?></td>
        <td><?= $l['recebeu'] ?></td>
        <td><?= ($l['troca']['extra_autorizado'] > 0 ? '+'.$l['troca']['extra_autorizado'].' troca ' : '') . ($l['passagem']['extra_autorizado'] > 0 ? '+'.$l['passagem']['extra_autorizado'].' passagem' : '') ?: '0' ?></td>
        <td><?= $l['pendente_flit'] ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($linhas)): ?><tr><td colspan="8" class="text-center text-muted">Nenhuma movimentação neste período.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>
