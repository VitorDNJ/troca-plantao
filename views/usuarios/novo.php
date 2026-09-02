<h4 class="mb-4">Novo Usuário</h4>

<div class="card card-dashboard p-4">
  <form method="post" action="<?= url('usuarios_novo.php') ?>">
    <?= \App\Core\Csrf::field() ?>
    <div class="row g-3 mb-3">
      <div class="col-md-3"><label class="form-label">Matrícula</label><input type="text" name="matricula" class="form-control" value="<?= old('matricula') ?>" required></div>
      <div class="col-md-5"><label class="form-label">Nome completo</label><input type="text" name="nome" class="form-control" value="<?= old('nome') ?>" required></div>
      <div class="col-md-4"><label class="form-label">CPF</label><input type="text" name="cpf" class="form-control" value="<?= old('cpf') ?>" required placeholder="Somente números"></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control" value="<?= old('email') ?>" required></div>
      <div class="col-md-4"><label class="form-label">Função</label><input type="text" name="funcao" class="form-control" value="<?= old('funcao') ?>" required></div>
      <div class="col-md-4"><label class="form-label">Perfil</label>
        <select name="perfil_id" class="form-select" required>
          <option value="">Selecione</option>
          <?php foreach ($perfis as $p): ?>
            <option value="<?= $p['id'] ?>"><?= h($p['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">Setor principal</label>
        <select name="setor_id" class="form-select" required>
          <option value="">Selecione</option>
          <?php foreach ($setores as $s): ?>
            <option value="<?= $s['id'] ?>"><?= h($s['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label">Setores adicionais (apenas para coordenadores)</label>
        <div class="border rounded p-2" style="max-height:120px; overflow:auto;">
          <?php foreach ($setores as $s): ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="setores_extra[]" value="<?= $s['id'] ?>" id="se<?= $s['id'] ?>">
              <label class="form-check-label" for="se<?= $s['id'] ?>"><?= h($s['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="alert alert-secondary small">Uma senha temporária será gerada automaticamente e exibida após o cadastro. O usuário deverá trocá-la no primeiro acesso.</div>
    <button type="submit" class="btn btn-primary">Cadastrar usuário</button>
    <a href="<?= url('usuarios_lista.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
  </form>
</div>
