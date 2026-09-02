<h4 class="mb-4">Olá, <?= h(\App\Core\Auth::nome()) ?> 👋</h4>

<?php if (!$periodo): ?>
  <div class="alert alert-warning">Nenhum período de controle ativo no momento. Procure o RH/Administrador.</div>
<?php else: ?>
  <div class="card card-dashboard p-3 mb-4">
    <h6 class="text-muted mb-3">PERÍODO ATUAL: <?= h($periodo['nome']) ?> (<?= formatarData($periodo['data_inicial']) ?> a <?= formatarData($periodo['data_final']) ?>)</h6>
    <div class="row g-3">
      <?php foreach (['troca' => 'TROCAS', 'passagem' => 'PASSAGENS'] as $chave => $titulo): $r = $resumo[$chave]; ?>
        <div class="col-md-6">
          <div class="border rounded p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
              <strong><?= $titulo ?></strong>
              <?php if ($r['em_excecao']): ?>
                <span class="badge bg-info limite-badge">EXCEÇÃO AUTORIZADA</span>
              <?php endif; ?>
            </div>
            <div class="valor mt-2"><?= $r['utilizadas'] ?>/<?= $r['limite_padrao'] ?><?= $r['extra_autorizado'] > 0 ? ' <small class="text-muted">(efetivo '.$r['limite_efetivo'].')</small>' : '' ?></div>
            <?php if ($r['disponivel'] > 0): ?>
              <div class="text-success"><?= $r['disponivel'] ?> disponível(is)</div>
            <?php else: ?>
              <div class="text-danger">Limite atingido</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <a href="<?= url('trocas_nova.php') ?>" class="btn btn-outline-primary w-100 py-3">➕ Solicitar Troca de Plantão</a>
    </div>
    <div class="col-md-6">
      <a href="<?= url('passagens_nova.php') ?>" class="btn btn-outline-primary w-100 py-3">➕ Solicitar Passagem de Plantão</a>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($recebidas)): ?>
<div class="card card-dashboard p-3 mb-4">
  <h6 class="mb-3">Solicitações aguardando sua resposta</h6>
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>De</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($recebidas as $r): ?>
      <tr>
        <td><?= h($r['codigo']) ?></td>
        <td><?= h($r['tipo']) ?></td>
        <td><?= h($r['solicitante_nome']) ?></td>
        <td><?= statusBadge($r['status']) ?></td>
        <td><a href="<?= url(($r['tipo']==='TROCA'?'trocas_ver.php':'passagens_ver.php').'?id='.$r['id']) ?>" class="btn btn-sm btn-primary">Responder</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<div class="card card-dashboard p-3">
  <h6 class="mb-3">Minhas últimas solicitações</h6>
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Tipo</th><th>Período</th><th>Status</th><th>FLIT</th><th></th></tr></thead>
    <tbody>
      <?php foreach (array_slice($minhas, 0, 10) as $s): ?>
      <tr>
        <td><?= h($s['codigo']) ?></td>
        <td><?= h($s['tipo']) ?></td>
        <td><?= h($s['periodo_nome']) ?></td>
        <td><?= statusBadge($s['status']) ?> <?= $s['possui_excecao'] ? '<span class="badge bg-info">Exceção</span>' : '' ?></td>
        <td><?= $s['flit_status'] === 'LANCADA_FLIT' ? '<span class="badge bg-success">Lançada</span>' : ($s['flit_status'] === 'PENDENTE_FLIT' ? '<span class="badge bg-warning text-dark">Pendente</span>' : '-') ?></td>
        <td><a href="<?= url(($s['tipo']==='TROCA'?'trocas_ver.php':'passagens_ver.php').'?id='.$s['id']) ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($minhas)): ?>
        <tr><td colspan="6" class="text-center text-muted">Nenhuma solicitação ainda.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
  <a href="<?= url('minhas_solicitacoes.php') ?>" class="btn btn-link">Ver histórico completo →</a>
</div>
