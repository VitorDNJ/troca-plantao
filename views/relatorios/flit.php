<h4 class="mb-4">Relatório para o FLIT</h4>

<div class="card card-dashboard p-3 mb-4">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-2">
      <label class="form-label small">Período</label>
      <select name="periodo_id" class="form-select form-select-sm">
        <option value="">Todos</option>
        <?php foreach ($periodos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $filtros['periodo_id']==$p['id']?'selected':'' ?>><?= h($p['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Setor</label>
      <select name="setor_id" class="form-select form-select-sm">
        <option value="">Todos</option>
        <?php foreach ($setores as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $filtros['setor_id']==$s['id']?'selected':'' ?>><?= h($s['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Tipo</label>
      <select name="tipo" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="TROCA" <?= $filtros['tipo']==='TROCA'?'selected':'' ?>>Troca</option>
        <option value="PASSAGEM" <?= $filtros['tipo']==='PASSAGEM'?'selected':'' ?>>Passagem</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Lançado no FLIT</label>
      <select name="flit_status" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="PENDENTE_FLIT" <?= $filtros['flit_status']==='PENDENTE_FLIT'?'selected':'' ?>>Pendente</option>
        <option value="LANCADA_FLIT" <?= $filtros['flit_status']==='LANCADA_FLIT'?'selected':'' ?>>Lançada</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Exceção</label>
      <select name="excecao" class="form-select form-select-sm">
        <option value="">Todas</option>
        <option value="1" <?= $filtros['excecao']==='1'?'selected':'' ?>>Somente com exceção</option>
        <option value="0" <?= $filtros['excecao']==='0'?'selected':'' ?>>Sem exceção</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
    </div>
  </form>
</div>

<div class="card card-dashboard p-3 mb-4">
  <h6>TROCAS</h6>
  <div class="table-responsive">
  <table class="table table-sm tabela-compacta">
    <thead><tr><th>Código</th><th>Colaborador</th><th>Matrícula</th><th>Dia original</th><th>Novo dia</th><th>Com quem trocou</th><th>Matrícula</th><th>Exceção</th><th>FLIT</th></tr></thead>
    <tbody>
      <?php foreach ($trocas as $t): ?>
      <tr>
        <td><?= h($t['codigo']) ?></td>
        <td><?= h($t['solicitante_nome']) ?></td>
        <td><?= h($t['solicitante_matricula']) ?></td>
        <td><?= formatarData($t['detalhe']['meu_data']) ?></td>
        <td><?= formatarData($t['detalhe']['outro_data']) ?></td>
        <td><?= h($t['detalhe']['outro_nome']) ?></td>
        <td><?= h($t['detalhe']['outro_matricula']) ?></td>
        <td><?= $t['possui_excecao'] ? 'Sim' : 'Não' ?></td>
        <td><?= $t['flit_status'] === 'LANCADA_FLIT' ? 'Lançada' : ($t['flit_status']==='PENDENTE_FLIT'?'Pendente':'-') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($trocas)): ?><tr><td colspan="9" class="text-center text-muted">Nenhum registro.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card card-dashboard p-3">
  <h6>PASSAGENS</h6>
  <div class="table-responsive">
  <table class="table table-sm tabela-compacta">
    <thead><tr><th>Código</th><th>Quem passou</th><th>Matrícula</th><th>Data</th><th>Quem recebeu</th><th>Matrícula</th><th>Exceção</th><th>FLIT</th></tr></thead>
    <tbody>
      <?php foreach ($passagens as $p): ?>
      <tr>
        <td><?= h($p['codigo']) ?></td>
        <td><?= h($p['solicitante_nome']) ?></td>
        <td><?= h($p['solicitante_matricula']) ?></td>
        <td><?= formatarData($p['detalhe']['data']) ?></td>
        <td><?= h($p['detalhe']['recebeu_nome']) ?></td>
        <td><?= h($p['detalhe']['recebeu_matricula']) ?></td>
        <td><?= $p['possui_excecao'] ? 'Sim' : 'Não' ?></td>
        <td><?= $p['flit_status'] === 'LANCADA_FLIT' ? 'Lançada' : ($p['flit_status']==='PENDENTE_FLIT'?'Pendente':'-') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($passagens)): ?><tr><td colspan="8" class="text-center text-muted">Nenhum registro.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
