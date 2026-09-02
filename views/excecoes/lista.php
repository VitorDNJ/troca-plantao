<h4 class="mb-4">Exceções de Limite Pendentes</h4>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Código</th><th>Colaborador</th><th>Setor</th><th>Tipo</th><th>Justificativa</th><th>Data</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($pendentes as $e): ?>
      <tr>
        <td><?= h($e['codigo']) ?></td>
        <td><?= h($e['usuario_nome']) ?> (<?= h($e['usuario_matricula']) ?>)</td>
        <td><?= h($e['setor_nome']) ?></td>
        <td><?= h($e['tipo']) ?></td>
        <td><?= h($e['justificativa']) ?></td>
        <td><?= formatarDataHora($e['criado_em']) ?></td>
        <td>
          <div class="d-flex gap-1">
            <form method="post" data-confirm="Autorizar esta exceção de limite?">
              <?= \App\Core\Csrf::field() ?>
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <input type="hidden" name="acao" value="autorizar">
              <button type="submit" class="btn btn-sm btn-success">Autorizar</button>
            </form>
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#negar<?= $e['id'] ?>">Negar</button>
          </div>
          <div class="collapse mt-2" id="negar<?= $e['id'] ?>">
            <form method="post">
              <?= \App\Core\Csrf::field() ?>
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <input type="hidden" name="acao" value="negar">
              <input type="text" name="motivo_negativa" class="form-control form-control-sm mb-1" placeholder="Motivo da negativa" required>
              <button type="submit" class="btn btn-sm btn-danger">Confirmar negativa</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($pendentes)): ?>
        <tr><td colspan="7" class="text-center text-muted">Nenhuma exceção pendente.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
