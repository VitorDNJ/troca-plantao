<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Troca <?= h($sol['codigo']) ?></h4>
  <?= statusBadge($sol['status']) ?>
</div>

<?php if ($sol['possui_excecao']): ?>
  <div class="alert alert-info">⚠️ Esta solicitação utiliza uma <strong>EXCEÇÃO AUTORIZADA</strong> de limite.</div>
<?php endif; ?>
<?php if ($sol['autorizado_entre_periodos']): ?>
  <div class="alert alert-secondary">ℹ️ Os plantões envolvidos pertencem a períodos de controle diferentes.</div>
<?php endif; ?>
<?php if ($sol['status'] === 'REPROVADA' && $sol['motivo_reprovacao']): ?>
  <div class="alert alert-danger"><strong>Motivo da reprovação:</strong> <?= h($sol['motivo_reprovacao']) ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-md-8">
    <div class="card card-dashboard p-4 mb-4">
      <h6 class="text-muted">SOLICITANTE</h6>
      <p class="mb-3"><?= h($sol['solicitante_nome']) ?> — matrícula <?= h($sol['solicitante_matricula']) ?> — <?= h($sol['solicitante_setor_nome']) ?> — <?= h($sol['solicitante_funcao']) ?></p>

      <h6 class="text-muted">MEU PLANTÃO (do solicitante)</h6>
      <p class="mb-3"><?= formatarData($troca['meu_data']) ?> das <?= formatarHora($troca['meu_hora_inicial']) ?> às <?= formatarHora($troca['meu_hora_final']) ?> — turno <?= h($troca['meu_turno']) ?></p>

      <h6 class="text-muted">OUTRO COLABORADOR</h6>
      <p class="mb-3"><?= h($troca['outro_nome']) ?> — matrícula <?= h($troca['outro_matricula']) ?> — <?= h($troca['outro_setor_nome']) ?></p>

      <h6 class="text-muted">PLANTÃO DO OUTRO COLABORADOR</h6>
      <p class="mb-3"><?= formatarData($troca['outro_data']) ?> das <?= formatarHora($troca['outro_hora_inicial']) ?> às <?= formatarHora($troca['outro_hora_final']) ?> — turno <?= h($troca['outro_turno']) ?></p>

      <?php if ($sol['motivo']): ?><h6 class="text-muted">MOTIVO</h6><p class="mb-3"><?= h($sol['motivo']) ?></p><?php endif; ?>
      <?php if ($sol['observacao']): ?><h6 class="text-muted">OBSERVAÇÃO</h6><p class="mb-0"><?= h($sol['observacao']) ?></p><?php endif; ?>
    </div>

    <?php if ($souOutro && $sol['status'] === 'PENDENTE_ACEITE'): ?>
    <div class="card card-dashboard p-4 mb-4 border-primary">
      <h6>Você recebeu esta solicitação de troca. Deseja aceitar?</h6>
      <form method="post" class="d-flex gap-2 mt-2">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="acao" value="aceitar">
        <button type="submit" class="btn btn-success">Aceitar</button>
      </form>
      <form method="post" class="mt-3">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="acao" value="recusar">
        <div class="mb-2"><input type="text" name="observacao_recusa" class="form-control" placeholder="Motivo da recusa (opcional)"></div>
        <button type="submit" class="btn btn-outline-danger">Recusar</button>
      </form>
    </div>
    <?php endif; ?>

    <?php if ($souSolicitante && in_array($sol['status'], ['PENDENTE_ACEITE','ACEITA','AGUARDANDO_COORDENADOR'], true)): ?>
      <form method="post" data-confirm="Tem certeza que deseja cancelar esta solicitação?">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="acao" value="cancelar">
        <button type="submit" class="btn btn-outline-danger">Cancelar solicitação</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="col-md-4">
    <div class="card card-dashboard p-4">
      <h6 class="mb-3">Linha do tempo</h6>
      <?php foreach ($historico as $h): ?>
        <div class="timeline-item">
          <div class="small text-muted"><?= formatarDataHora($h['criado_em']) ?></div>
          <div><?= h($h['acao']) ?><?= $h['usuario_nome'] ? ' — ' . h($h['usuario_nome']) : '' ?></div>
          <?php if ($h['observacao']): ?><div class="small text-muted">"<?= h($h['observacao']) ?>"</div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
