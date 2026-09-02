<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card card-dashboard p-4">
      <h5 class="mb-3">Alterar senha</h5>
      <?php if (\App\Core\Auth::precisaTrocarSenha()): ?>
        <div class="alert alert-warning">Por segurança, você precisa trocar sua senha temporária antes de continuar.</div>
      <?php endif; ?>
      <form method="post" action="<?= url('trocar_senha.php') ?>">
        <?= \App\Core\Csrf::field() ?>
        <div class="mb-3">
          <label class="form-label">Senha atual</label>
          <input type="password" name="senha_atual" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nova senha (mín. 8 caracteres)</label>
          <input type="password" name="senha_nova" class="form-control" minlength="8" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirmar nova senha</label>
          <input type="password" name="senha_confirmacao" class="form-control" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Salvar nova senha</button>
      </form>
    </div>
  </div>
</div>
