<h4 class="mb-4">Setores</h4>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card card-dashboard p-3">
      <h6>Novo setor</h6>
      <form method="post">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="acao" value="criar">
        <div class="mb-2"><input type="text" name="nome" class="form-control" placeholder="Nome do setor" required></div>
        <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card card-dashboard p-3">
      <table class="table tabela-compacta">
        <thead><tr><th>Nome</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($setores as $s): ?>
          <tr>
            <td>
              <form method="post" class="d-flex gap-2">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <input type="text" name="nome" value="<?= h($s['nome']) ?>" class="form-control form-control-sm">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Salvar</button>
              </form>
            </td>
            <td><span class="badge bg-<?= $s['status']==='ATIVO'?'success':'secondary' ?>"><?= h($s['status']) ?></span></td>
            <td>
              <form method="post" data-confirm="Confirma alterar o status deste setor?">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="acao" value="alternar_status">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-<?= $s['status']==='ATIVO'?'danger':'success' ?>"><?= $s['status']==='ATIVO'?'Desativar':'Ativar' ?></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
