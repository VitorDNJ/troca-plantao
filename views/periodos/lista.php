<h4 class="mb-4">Períodos de Controle</h4>

<div class="row g-4">
  <div class="col-md-5">
    <div class="card card-dashboard p-3">
      <h6>Novo período</h6>
      <form method="post">
        <?= \App\Core\Csrf::field() ?>
        <div class="mb-2"><label class="form-label small">Nome</label><input type="text" name="nome" class="form-control" required></div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label small">Data inicial</label><input type="date" name="data_inicial" class="form-control" required></div>
          <div class="col-6"><label class="form-label small">Data final</label><input type="date" name="data_final" class="form-control" required></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6"><label class="form-label small">Limite de trocas</label><input type="number" min="0" name="limite_trocas" class="form-control" value="2" required></div>
          <div class="col-6"><label class="form-label small">Limite de passagens</label><input type="number" min="0" name="limite_passagens" class="form-control" value="2" required></div>
        </div>
        <div class="mb-2"><label class="form-label small">Status</label>
          <select name="status" class="form-select">
            <option value="FUTURO">Futuro</option>
            <option value="ATIVO">Ativo</option>
            <option value="ENCERRADO">Encerrado</option>
            <option value="INATIVO">Inativo</option>
          </select>
        </div>
        <div class="mb-2"><label class="form-label small">Troca entre períodos</label>
          <select name="regra_troca_entre_periodos" class="form-select">
            <option value="SOMENTE_AUTORIZACAO" selected>Somente com autorização do coordenador</option>
            <option value="PERMITIDA">Permitida</option>
            <option value="PROIBIDA">Proibida</option>
          </select>
        </div>
        <div class="mb-2"><label class="form-label small">Observação</label><textarea name="observacao" class="form-control"></textarea></div>
        <button type="submit" class="btn btn-primary w-100">Cadastrar período</button>
      </form>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card card-dashboard p-3">
      <div class="table-responsive">
      <table class="table tabela-compacta">
        <thead><tr><th>Nome</th><th>Datas</th><th>Limites</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($periodos as $p): ?>
          <tr>
            <td><?= h($p['nome']) ?></td>
            <td><?= formatarData($p['data_inicial']) ?> a <?= formatarData($p['data_final']) ?></td>
            <td>Trocas: <?= $p['limite_trocas'] ?> / Passagens: <?= $p['limite_passagens'] ?></td>
            <td><span class="badge bg-<?= $p['status']==='ATIVO'?'success':($p['status']==='FUTURO'?'info':'secondary') ?>"><?= h($p['status']) ?></span></td>
            <td><a href="<?= url('periodos_editar.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>
