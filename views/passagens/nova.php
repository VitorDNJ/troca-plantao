<h4 class="mb-4">Solicitar Passagem de Plantão</h4>

<?php if ($limiteAtingido): ?>
  <div class="card card-dashboard p-4 mb-4 border-danger">
    <h5 class="text-danger">Você atingiu o limite de passagens neste período.</h5>
    <p class="text-muted">Se realmente precisar realizar esta passagem, você pode solicitar uma autorização excepcional ao coordenador.</p>
    <div>
      <a href="<?= url('excecoes_solicitar.php?tipo=PASSAGEM') ?>" class="btn btn-warning me-2">SOLICITAR AUTORIZAÇÃO EXCEPCIONAL</a>
      <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">VOLTAR</a>
    </div>
  </div>
<?php endif; ?>

<?php if ($mensagemErro): ?>
  <div class="alert alert-danger"><?= h($mensagemErro) ?></div>
<?php endif; ?>

<div class="card card-dashboard p-4">
  <form method="post" action="<?= url('passagens_nova.php') ?>">
    <?= \App\Core\Csrf::field() ?>

    <h6 class="text-muted">QUEM PASSA</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-3"><label class="form-label">Nome</label><input class="form-control" value="<?= h($solicitante['nome']) ?>" disabled></div>
      <div class="col-md-3"><label class="form-label">Matrícula</label><input class="form-control" value="<?= h($solicitante['matricula']) ?>" disabled></div>
      <div class="col-md-3"><label class="form-label">Setor</label><input class="form-control" value="<?= h($solicitante['setor_nome']) ?>" disabled></div>
      <div class="col-md-3"><label class="form-label">Função</label><input class="form-control" value="<?= h($solicitante['funcao']) ?>" disabled></div>
    </div>

    <h6 class="text-muted">PLANTÃO A SER PASSADO</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-3"><label class="form-label">Data</label><input type="date" name="data" class="form-control" value="<?= old('data') ?>" required></div>
      <div class="col-md-3"><label class="form-label">Horário inicial</label><input type="time" name="hora_inicial" class="form-control" value="<?= old('hora_inicial') ?>" required></div>
      <div class="col-md-3"><label class="form-label">Horário final</label><input type="time" name="hora_final" class="form-control" value="<?= old('hora_final') ?>" required></div>
      <div class="col-md-3"><label class="form-label">Turno</label>
        <select name="turno" class="form-select" required>
          <option value="">Selecione</option>
          <option value="SD" <?= old('turno')==='SD'?'selected':'' ?>>SD (Diurno)</option>
          <option value="SN" <?= old('turno')==='SN'?'selected':'' ?>>SN (Noturno)</option>
        </select>
      </div>
    </div>

    <h6 class="text-muted">QUEM VAI RECEBER</h6>
    <div class="row g-3 mb-2">
      <div class="col-md-6">
        <label class="form-label">Pesquisar por nome ou matrícula</label>
        <input type="text" id="buscaColaborador" class="form-control" placeholder="Digite o nome ou matrícula..." autocomplete="off">
        <div id="resultadoBusca" class="list-group position-relative" style="z-index:10;"></div>
        <input type="hidden" name="quem_recebeu_id" id="outroUsuarioId" value="<?= old('quem_recebeu_id') ?>">
        <div id="infoColaborador"></div>
      </div>
    </div>

    <h6 class="text-muted mt-3">OUTROS</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">Motivo</label><input type="text" name="motivo" class="form-control" value="<?= old('motivo') ?>"></div>
      <div class="col-md-6"><label class="form-label">Observação</label><input type="text" name="observacao" class="form-control" value="<?= old('observacao') ?>"></div>
    </div>

    <button type="submit" class="btn btn-primary">Enviar solicitação</button>
    <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </form>
</div>

<script>
initBuscaColaborador('buscaColaborador', 'resultadoBusca', 'outroUsuarioId', 'infoColaborador', <?= (int)\App\Core\Auth::id() ?>);
</script>
