<h4 class="mb-4">Relatório Individual</h4>

<div class="card card-dashboard p-3 mb-4">
  <form method="get" class="row g-2">
    <div class="col-md-8">
      <label class="form-label small">Pesquisar colaborador (nome ou matrícula)</label>
      <input type="text" name="q" class="form-control" value="<?= h($termo) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Pesquisar</button>
    </div>
  </form>
  <?php if ($resultadosBusca): ?>
    <div class="list-group mt-2">
      <?php foreach ($resultadosBusca as $r): ?>
        <a class="list-group-item list-group-item-action" href="<?= url('relatorios_individual.php?usuario_id=' . $r['id']) ?>">
          <?= h($r['nome']) ?> — <?= h($r['matricula']) ?> — <?= h($r['setor_nome']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($usuario): ?>
<div class="card card-dashboard p-3 mb-4">
  <h6>Nome: <?= h($usuario['nome']) ?></h6>
  <p class="text-muted mb-2">Matrícula: <?= h($usuario['matricula']) ?> &middot; Setor: <?= h($usuario['setor_nome']) ?></p>

  <form method="get" class="row g-2 mb-3">
    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
    <div class="col-md-4">
      <select name="periodo_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php foreach ($periodos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $periodoId==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <div class="row g-3 mb-3">
    <div class="col-md-3"><div class="border rounded p-2 text-center"><div class="small text-muted">TOTAL TROCAS</div><div class="valor"><?= (int)($totais['total_trocas'] ?? 0) ?></div></div></div>
    <div class="col-md-3"><div class="border rounded p-2 text-center"><div class="small text-muted">PASSAGENS REALIZADAS</div><div class="valor"><?= (int)($totais['total_passagens_realizadas'] ?? 0) ?></div></div></div>
    <div class="col-md-3"><div class="border rounded p-2 text-center"><div class="small text-muted">PASSAGENS RECEBIDAS</div><div class="valor"><?= (int)($totais['total_passagens_recebidas'] ?? 0) ?></div></div></div>
    <div class="col-md-3"><div class="border rounded p-2 text-center"><div class="small text-muted">EXCEÇÕES AUTORIZADAS</div><div class="valor"><?= (int)($totais['total_excecoes'] ?? 0) ?></div></div></div>
  </div>

  <?php if ($resumoLimite): ?>
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="border rounded p-2">LIMITE DE TROCAS: <?= $resumoLimite['troca']['utilizadas'] ?>/<?= $resumoLimite['troca']['limite_padrao'] ?>
        <?= $resumoLimite['troca']['em_excecao'] ? '<span class="badge bg-info">Exceção</span>' : '' ?></div>
    </div>
    <div class="col-md-6">
      <div class="border rounded p-2">LIMITE DE PASSAGENS: <?= $resumoLimite['passagem']['utilizadas'] ?>/<?= $resumoLimite['passagem']['limite_padrao'] ?>
        <?= $resumoLimite['passagem']['em_excecao'] ? '<span class="badge bg-info">Exceção</span>' : '' ?></div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="card card-dashboard p-3">
  <h6 class="mb-3">Histórico detalhado</h6>
  <div class="table-responsive">
  <table class="table table-sm tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>Período</th><th>Status</th><th>Exceção</th><th>FLIT</th><th>Criado em</th></tr></thead>
    <tbody>
      <?php foreach ($historicoCompleto as $h): ?>
      <tr>
        <td><?= h($h['codigo']) ?></td>
        <td><?= h($h['tipo']) ?></td>
        <td><?= h($h['periodo_nome']) ?></td>
        <td><?= statusBadge($h['status']) ?></td>
        <td><?= $h['possui_excecao'] ? 'Sim' : 'Não' ?></td>
        <td><?= $h['flit_status'] === 'LANCADA_FLIT' ? 'Lançada' : ($h['flit_status']==='PENDENTE_FLIT'?'Pendente':'-') ?></td>
        <td><?= formatarDataHora($h['criado_em']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>
