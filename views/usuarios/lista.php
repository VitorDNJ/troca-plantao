<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Usuários</h4>
  <a href="<?= url('usuarios_novo.php') ?>" class="btn btn-primary">+ Novo usuário</a>
</div>

<div class="card card-dashboard p-3 mb-4">
  <form method="get" class="row g-2">
    <div class="col-md-6"><input type="text" name="busca" class="form-control" placeholder="Nome, matrícula ou CPF" value="<?= h($busca) ?>"></div>
    <div class="col-md-4">
      <select name="setor_id" class="form-select">
        <option value="">Todos os setores</option>
        <?php foreach ($setores as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $setorId==$s['id']?'selected':'' ?>><?= h($s['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Filtrar</button></div>
  </form>
</div>

<div class="card card-dashboard p-3">
  <div class="table-responsive">
  <table class="table tabela-compacta">
    <thead><tr><th>Matrícula</th><th>Nome</th><th>Setor</th><th>Perfil</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= h($u['matricula']) ?></td>
        <td><?= h($u['nome']) ?></td>
        <td><?= h($u['setor_nome']) ?></td>
        <td><?= h($u['perfil_nome']) ?></td>
        <td><span class="badge bg-<?= $u['status']==='ATIVO'?'success':'secondary' ?>"><?= h($u['status']) ?></span></td>
        <td>
          <a href="<?= url('usuarios_editar.php?id=' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
          <form method="post" action="<?= url('usuarios_acao.php') ?>" class="d-inline" data-confirm="Confirma alterar o status deste usuário?">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <input type="hidden" name="acao" value="alternar_status">
            <button type="submit" class="btn btn-sm btn-outline-<?= $u['status']==='ATIVO'?'danger':'success' ?>"><?= $u['status']==='ATIVO'?'Desativar':'Ativar' ?></button>
          </form>
          <form method="post" action="<?= url('usuarios_acao.php') ?>" class="d-inline" data-confirm="Gerar nova senha temporária para este usuário?">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <input type="hidden" name="acao" value="senha_temporaria">
            <button type="submit" class="btn btn-sm btn-outline-warning">Senha temporária</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($usuarios)): ?><tr><td colspan="6" class="text-center text-muted">Nenhum usuário encontrado.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
