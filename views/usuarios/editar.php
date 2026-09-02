<h4 class="mb-4">Editar Usuário</h4>

<div class="card card-dashboard p-4">
  <form method="post">
    <?= \App\Core\Csrf::field() ?>
    <div class="row g-3 mb-3">
      <div class="col-md-3"><label class="form-label">Matrícula</label><input type="text" class="form-control" value="<?= h($usuario['matricula']) ?>" disabled></div>
      <div class="col-md-5"><label class="form-label">Nome completo</label><input type="text" name="nome" class="form-control" value="<?= h($usuario['nome']) ?>" required></div>
      <div class="col-md-4"><label class="form-label">CPF</label><input type="text" name="cpf" class="form-control" value="<?= h($usuario['cpf']) ?>" required></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control" value="<?= h($usuario['email']) ?>" required></div>
      <div class="col-md-4"><label class="form-label">Função</label><input type="text" name="funcao" class="form-control" value="<?= h($usuario['funcao']) ?>" required></div>
      <div class="col-md-4"><label class="form-label">Perfil</label>
        <select name="perfil_id" class="form-select" required>
          <?php foreach ($perfis as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $p['id']==$usuario['perfil_id']?'selected':'' ?>><?= h($p['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">Setor principal</label>
        <select name="setor_id" class="form-select" required>
          <?php foreach ($setores as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $s['id']==$usuario['setor_id']?'selected':'' ?>><?= h($s['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4"><label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="ATIVO" <?= $usuario['status']==='ATIVO'?'selected':'' ?>>Ativo</option>
          <option value="INATIVO" <?= $usuario['status']==='INATIVO'?'selected':'' ?>>Inativo</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Setores adicionais</label>
        <div class="border rounded p-2" style="max-height:120px; overflow:auto;">
          <?php foreach ($setores as $s): ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="setores_extra[]" value="<?= $s['id'] ?>" id="se<?= $s['id'] ?>" <?= in_array($s['id'], $setoresVinculados) ? 'checked' : '' ?>>
              <label class="form-check-label" for="se<?= $s['id'] ?>"><?= h($s['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Salvar alterações</button>
    <a href="<?= url('usuarios_lista.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </form>
</div>
