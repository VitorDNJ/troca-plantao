<h4 class="mb-4">Notificações</h4>

<div class="card card-dashboard p-3">
  <?php if (empty($notificacoes)): ?>
    <p class="text-muted text-center mb-0">Nenhuma notificação ainda.</p>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($notificacoes as $n): ?>
        <a href="<?= $n['link'] ? url($n['link']) : '#' ?>" class="list-group-item list-group-item-action <?= $n['lida'] ? '' : 'list-group-item-primary' ?>">
          <div class="d-flex justify-content-between">
            <span><?= h($n['mensagem']) ?></span>
            <small class="text-muted"><?= formatarDataHora($n['criado_em']) ?></small>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
