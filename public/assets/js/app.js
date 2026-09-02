// Autocomplete de colaborador (troca/passagem) usando Fetch API
function initBuscaColaborador(inputId, resultId, hiddenId, infoId, excluirId) {
  const input = document.getElementById(inputId);
  const resultBox = document.getElementById(resultId);
  const hidden = document.getElementById(hiddenId);
  const info = document.getElementById(infoId);
  if (!input) return;

  let timer = null;

  input.addEventListener('input', function () {
    hidden.value = '';
    if (info) info.innerHTML = '';
    clearTimeout(timer);
    const termo = input.value.trim();
    if (termo.length < 2) {
      resultBox.innerHTML = '';
      return;
    }
    timer = setTimeout(() => buscar(termo), 300);
  });

  function buscar(termo) {
    const params = new URLSearchParams({ q: termo });
    if (excluirId) params.append('excluir', excluirId);

    fetch('buscar_colaborador.php?' + params.toString())
      .then(r => r.json())
      .then(data => {
        resultBox.innerHTML = '';
        if (!data.resultados || data.resultados.length === 0) {
          resultBox.innerHTML = '<div class="list-group-item text-muted">Nenhum colaborador encontrado</div>';
          return;
        }
        data.resultados.forEach(u => {
          const item = document.createElement('button');
          item.type = 'button';
          item.className = 'list-group-item list-group-item-action';
          item.textContent = `${u.nome} — matrícula ${u.matricula} (${u.setor_nome})`;
          item.addEventListener('click', () => {
            hidden.value = u.id;
            input.value = u.nome;
            resultBox.innerHTML = '';
            if (info) {
              info.innerHTML = `<div class="alert alert-light border mt-2 mb-0 py-2">
                <strong>${u.nome}</strong><br>Matrícula: ${u.matricula} &middot; Setor: ${u.setor_nome} &middot; Função: ${u.funcao ?? ''}
              </div>`;
            }
          });
          resultBox.appendChild(item);
        });
      })
      .catch(() => {
        resultBox.innerHTML = '<div class="list-group-item text-danger">Erro ao buscar colaborador</div>';
      });
  }
}

// Confirmação simples antes de ações destrutivas/importantes
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });
});
