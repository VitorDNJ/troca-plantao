<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0"><?= h($sol['tipo']) ?> <?= h($sol['codigo']) ?></h4>
  <?= statusBadge($sol['status']) ?>
</div>

<?php if ($situacaoLimite['em_excecao'] || $situacaoLimite['utilizadas'] > $situacaoLimite['limite_padrao']): ?>
  <div class="alert alert-warning">
    <strong>ATENÇÃO:</strong> Esta solicitação utiliza uma <strong>EXCEÇÃO AUTORIZADA</strong>.
    <?php if ($excecaoInfo): ?>
      <div class="mt-2 small">
        Autorizado por: <?= h($excecaoInfo['autorizador_nome'] ?? '-') ?><br>
        Justificativa: <?= h($excecaoInfo['justificativa']) ?><br>
        Data: <?= formatarDataHora($excecaoInfo['autorizado_em']) ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-md-8">
    <div class="card card-dashboard p-4 mb-4">
      <table class="table table-borderless mb-0">
        <tr><th style="width:220px;">Solicitante</th><td><?= h($sol['solicitante_nome']) ?> (<?= h($sol['solicitante_matricula']) ?>)</td></tr>
        <tr><th>Setor</th><td><?= h($sol['solicitante_setor_nome']) ?></td></tr>
        <tr><th>Período</th><td><?= h($sol['periodo_nome']) ?> (<?= formatarData($sol['periodo_data_inicial']) ?> a <?= formatarData($sol['periodo_data_final']) ?>)</td></tr>

        <?php if ($sol['tipo'] === 'TROCA'): ?>
          <tr><th>Meu plantão</th><td><?= formatarData($detalhe['meu_data']) ?> (<?= h($detalhe['meu_turno']) ?>)</td></tr>
          <tr><th>Outro colaborador</th><td><?= h($detalhe['outro_nome']) ?> (<?= h($detalhe['outro_matricula']) ?>) — <?= h($detalhe['outro_setor_nome']) ?></td></tr>
          <tr><th>Plantão do outro</th><td><?= formatarData($detalhe['outro_data']) ?> (<?= h($detalhe['outro_turno']) ?>)</td></tr>
        <?php else: ?>
          <tr><th>Recebeu</th><td><?= h($detalhe['recebeu_nome']) ?> (<?= h($detalhe['recebeu_matricula']) ?>) — <?= h($detalhe['recebeu_setor_nome']) ?></td></tr>
          <tr><th>Plantão</th><td><?= formatarData($detalhe['data']) ?> <?= formatarHora($detalhe['hora_inicial']) ?>–<?= formatarHora($detalhe['hora_final']) ?> (<?= h($detalhe['turno']) ?>)</td></tr>
        <?php endif; ?>

        <tr><th>Motivo</th><td><?= h($sol['motivo'] ?: '-') ?></td></tr>
        <tr><th>Observação</th><td><?= h($sol['observacao'] ?: '-') ?></td></tr>
        <tr><th>Limite <?= strtolower($sol['tipo']) ?></th>
            <td><?= $situacaoLimite['utilizadas'] ?>/<?= $situacaoLimite['limite_padrao'] ?>
                <?= $situacaoLimite['extra_autorizado'] > 0 ? ' (efetivo ' . $situacaoLimite['limite_efetivo'] . ')' : '' ?></td></tr>
      </table>
    </div>

    <?php if ($sol['status'] === 'AGUARDANDO_COORDENADOR'): ?>
    <div class="card card-dashboard p-4">
      <div class="d-flex gap-2 mb-3">
        <form method="post">
          <?= \App\Core\Csrf::field() ?>
          <input type="hidden" name="acao" value="aprovar">
          <button type="submit" class="btn btn-success" data-confirm="Confirmar aprovação desta solicitação?">APROVAR</button>
        </form>
      </div>
      <form method="post" data-confirm="Confirmar reprovação desta solicitação?">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="acao" value="reprovar">
        <label class="form-label">Motivo da reprovação (obrigatório)</label>
        <textarea name="motivo_reprovacao" class="form-control mb-2" required></textarea>
        <button type="submit" class="btn btn-outline-danger">REPROVAR</button>
      </form>
    </div>
    <?php else: ?>
      <div class="alert alert-secondary">Esta solicitação já foi processada (<?= h(\App\Helpers\StatusSolicitacao::label($sol['status'])) ?>).</div>
    <?php endif; ?>
  </div>

  <div class="col-md-4">
    <div class="card card-dashboard p-4">
      <h6 class="mb-3">Linha do tempo</h6>
      <?php foreach ($historico as $h): ?>
        <div class="timeline-item">
          <div class="small text-muted"><?= formatarDataHora($h['criado_em']) ?></div>
          <div><?= h($h['acao']) ?><?= $h['usuario_nome'] ? ' — ' . h($h['usuario_nome']) : '' ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
