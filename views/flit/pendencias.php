<h4 class="mb-4">Pendências de Lançamento no FLIT</h4>

<form method="post">
  <?= \App\Core\Csrf::field() ?>
  <div class="card card-dashboard p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <input type="checkbox" id="marcarTodos" onclick="document.querySelectorAll('.chk-flit').forEach(c=>c.checked=this.checked)">
        <label for="marcarTodos" class="ms-1">Selecionar todos</label>
      </div>
      <button type="submit" class="btn btn-warning" data-confirm="Marcar as solicitações selecionadas como lançadas no FLIT?">MARCAR SELECIONADAS COMO LANÇADAS</button>
    </div>
    <div class="table-responsive">
    <table class="table tabela-compacta">
      <thead><tr><th></th><th>Código</th><th>Tipo</th><th>Colaborador</th><th>Matrícula</th><th>Setor</th><th>Exceção</th></tr></thead>
      <tbody>
        <?php foreach ($pendentes as $p): ?>
        <tr>
          <td><input type="checkbox" class="chk-flit" name="ids[]" value="<?= $p['id'] ?>"></td>
          <td><?= h($p['codigo']) ?></td>
          <td><?= h($p['tipo']) ?></td>
          <td><?= h($p['solicitante_nome']) ?></td>
          <td><?= h($p['solicitante_matricula']) ?></td>
          <td><?= h($p['setor_nome']) ?></td>
          <td><?= $p['possui_excecao'] ? 'Sim' : 'Não' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($pendentes)): ?>
          <tr><td colspan="7" class="text-center text-muted">Nenhuma pendência de lançamento.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</form>
