<h4 class="mb-4">Solicitar Autorização Excepcional</h4>

<div class="card card-dashboard p-4">
  <p class="text-muted">Período: <?= h($periodo['nome']) ?></p>
  <form method="post" action="<?= url('excecoes_solicitar.php') ?>">
    <?= \App\Core\Csrf::field() ?>
    <div class="mb-3">
      <label class="form-label">Tipo</label>
      <select name="tipo" class="form-select">
        <option value="TROCA" <?= $tipo==='TROCA'?'selected':'' ?>>Troca</option>
        <option value="PASSAGEM" <?= $tipo==='PASSAGEM'?'selected':'' ?>>Passagem</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Motivo / Justificativa</label>
      <textarea name="justificativa" class="form-control" rows="4" required placeholder="Explique a necessidade excepcional..."></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Enviar pedido ao coordenador</button>
    <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </form>
</div>
