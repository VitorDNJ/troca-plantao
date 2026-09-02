<h4 class="mb-4">Editar Período</h4>

<?php if ($possuiVinculo): ?>
  <div class="alert alert-info">Este período já possui solicitações vinculadas. Ele não pode ser excluído, mas pode ser editado.</div>
<?php endif; ?>

<div class="card card-dashboard p-4" style="max-width:600px;">
  <form method="post">
    <?= \App\Core\Csrf::field() ?>
    <div class="mb-2"><label class="form-label small">Nome</label><input type="text" name="nome" class="form-control" value="<?= h($periodo['nome']) ?>" required></div>
    <div class="row g-2 mb-2">
      <div class="col-6"><label class="form-label small">Data inicial</label><input type="date" name="data_inicial" class="form-control" value="<?= h($periodo['data_inicial']) ?>" required></div>
      <div class="col-6"><label class="form-label small">Data final</label><input type="date" name="data_final" class="form-control" value="<?= h($periodo['data_final']) ?>" required></div>
    </div>
    <div class="row g-2 mb-2">
      <div class="col-6"><label class="form-label small">Limite de trocas</label><input type="number" min="0" name="limite_trocas" class="form-control" value="<?= (int)$periodo['limite_trocas'] ?>" required></div>
      <div class="col-6"><label class="form-label small">Limite de passagens</label><input type="number" min="0" name="limite_passagens" class="form-control" value="<?= (int)$periodo['limite_passagens'] ?>" required></div>
    </div>
    <div class="mb-2"><label class="form-label small">Status</label>
      <select name="status" class="form-select">
        <?php foreach (['FUTURO','ATIVO','ENCERRADO','INATIVO'] as $st): ?>
          <option value="<?= $st ?>" <?= $periodo['status']===$st?'selected':'' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-2"><label class="form-label small">Troca entre períodos</label>
      <select name="regra_troca_entre_periodos" class="form-select">
        <?php foreach (['SOMENTE_AUTORIZACAO'=>'Somente com autorização','PERMITIDA'=>'Permitida','PROIBIDA'=>'Proibida'] as $val=>$label): ?>
          <option value="<?= $val ?>" <?= $periodo['regra_troca_entre_periodos']===$val?'selected':'' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3"><label class="form-label small">Observação</label><textarea name="observacao" class="form-control"><?= h($periodo['observacao']) ?></textarea></div>
    <button type="submit" class="btn btn-primary">Salvar alterações</button>
    <a href="<?= url('periodos_lista.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </form>
</div>
