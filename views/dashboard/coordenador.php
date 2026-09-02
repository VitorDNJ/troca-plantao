<h4 class="mb-4">Painel do Coordenador</h4>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card card-dashboard p-3 text-center">
      <div class="text-muted">Aguardando aprovação</div>
      <div class="valor text-primary"><?= count($aguardando) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-dashboard p-3 text-center">
      <div class="text-muted">Pendentes FLIT</div>
      <div class="valor text-warning"><?= count($pendentesFlit) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-dashboard p-3 text-center">
      <div class="text-muted">Exceções pendentes</div>
      <div class="valor text-info"><?= count($excecoesPendentes) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-dashboard p-3 text-center">
      <div class="text-muted">Período ativo</div>
      <div class="fw-bold mt-2"><?= $periodo ? h($periodo['nome']) : '—' ?></div>
    </div>
  </div>
</div>

<div class="card card-dashboard p-3 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Solicitações aguardando sua aprovação</h6>
    <a href="<?= url('aprovacoes_lista.php') ?>" class="btn btn-sm btn-primary">Ver todas</a>
  </div>
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>Solicitante</th><th>Setor</th><th>Criado em</th><th></th></tr></thead>
    <tbody>
      <?php foreach (array_slice($aguardando, 0, 8) as $s): ?>
      <tr>
        <td><?= h($s['codigo']) ?></td>
        <td><?= h($s['tipo']) ?></td>
        <td><?= h($s['solicitante_nome']) ?> (<?= h($s['solicitante_matricula']) ?>)</td>
        <td><?= h($s['setor_nome']) ?></td>
        <td><?= formatarDataHora($s['criado_em']) ?></td>
        <td><a href="<?= url('aprovacoes_ver.php?id=' . $s['id']) ?>" class="btn btn-sm btn-outline-primary">Analisar</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($aguardando)): ?><tr><td colspan="6" class="text-center text-muted">Nenhuma pendência.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card card-dashboard p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Pendências de lançamento no FLIT</h6>
    <a href="<?= url('flit_pendencias.php') ?>" class="btn btn-sm btn-warning">Ver todas</a>
  </div>
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>Colaborador</th><th>Setor</th><th>Exceção</th></tr></thead>
    <tbody>
      <?php foreach (array_slice($pendentesFlit, 0, 8) as $s): ?>
      <tr>
        <td><?= h($s['codigo']) ?></td>
        <td><?= h($s['tipo']) ?></td>
        <td><?= h($s['solicitante_nome']) ?> (<?= h($s['solicitante_matricula']) ?>)</td>
        <td><?= h($s['setor_nome']) ?></td>
        <td><?= $s['possui_excecao'] ? 'Sim' : 'Não' ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($pendentesFlit)): ?><tr><td colspan="5" class="text-center text-muted">Nenhuma pendência.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
