<div class="page-header">
  <div class="page-header__text">
    <h1>Lançar Frequência</h1>
    <p><?= e($turma['nome']) ?> › <?= e($disciplina['nome']) ?></p>
  </div>
  <div class="page-header__actions">
    <button type="button" class="btn btn--ghost btn--sm" onclick="openModal('modalImportFreq')">
      📤 Importar Excel
    </button>
  </div>
</div>

<form method="POST" action="/frequencia/salvar" id="formFrequencia" data-loading>
  <?= csrfField() ?>
  <input type="hidden" name="turma_id" value="<?= $turma['id'] ?>">
  <input type="hidden" name="disciplina_id" value="<?= $disciplina['id'] ?>">

  <div class="card">
    <div class="card__header">
      <div>
        <h3 class="card__title">Presença</h3>
        <p class="text-small text-muted"><?= count($alunos) ?> alunos</p>
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <div class="form-group" style="margin:0">
          <label class="form-label" for="data_aula" style="font-size:.8rem;margin-bottom:.25rem">Data da Aula</label>
          <input 
            type="date" 
            id="data_aula" 
            name="data_aula" 
            class="form-control" 
            value="<?= date('Y-m-d') ?>"
            required
            style="width:150px"
          >
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label" for="numero_aulas" style="font-size:.8rem;margin-bottom:.25rem">Nº Aulas</label>
          <input 
            type="number" 
            id="numero_aulas" 
            name="numero_aulas" 
            class="form-control" 
            value="1"
            min="1"
            max="10"
            required
            style="width:80px;text-align:center"
          >
        </div>
      </div>
    </div>

    <div class="card__body" style="padding:0">
      
      <div style="padding:1rem;background:var(--bg);border-bottom:1px solid var(--border)">
        <label class="form-check">
          <input type="checkbox" id="checkAll" onclick="marcarTodos(this)">
          <span style="font-weight:600">Marcar todos como presentes</span>
        </label>
      </div>

      <div class="table-wrapper">
        <table class="table table--compact">
          <thead>
            <tr>
              <th style="width:50px;text-align:center">
                <input type="checkbox" id="checkAllHeader" onclick="marcarTodos(this)" title="Marcar todos">
              </th>
              <th>Aluno</th>
              <th style="width:120px;text-align:center">Presença</th>
              <th style="width:200px">Observação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($alunos as $aluno): ?>
              <tr>
                <td style="text-align:center">
                  <input 
                    type="checkbox" 
                    class="check-presente"
                    name="presenca[<?= $aluno['id'] ?>]"
                    value="1"
                    checked
                  >
                </td>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="avatar avatar--sm"><?= initials($aluno['nome_completo']) ?></div>
                    <span><?= e($aluno['nome_completo']) ?></span>
                  </div>
                </td>
                <td style="text-align:center">
                  <span class="badge badge--success presenca-badge-<?= $aluno['id'] ?>">Presente</span>
                </td>
                <td>
                  <input 
                    type="text" 
                    name="observacao[<?= $aluno['id'] ?>]"
                    class="form-control form-control--sm"
                    placeholder="Motivo da falta..."
                    style="font-size:.85rem"
                  >
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
    
    <div class="card__footer">
      <div style="display:flex;gap:.75rem;justify-content:space-between;flex-wrap:wrap;align-items:center">
        <div class="form-group" style="margin:0;max-width:400px;flex:1">
          <label class="form-label" for="conteudo_dado" style="font-size:.85rem;margin-bottom:.25rem">
            Conteúdo dado na aula (opcional)
          </label>
          <input 
            type="text" 
            id="conteudo_dado" 
            name="conteudo_dado" 
            class="form-control"
            placeholder="Ex: Equações do 2º grau"
            style="font-size:.9rem"
          >
        </div>
        <div style="display:flex;gap:.75rem">
          <a href="/frequencia" class="btn btn--ghost">Voltar</a>
          <button type="submit" class="btn btn--primary">✓ Salvar Frequência</button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Modal Import Excel Frequência -->
<div class="modal-backdrop" id="modalImportFreq" style="display:none">
  <div class="modal">
    <div class="modal__header">
      <h3 class="modal__title">Importar Frequência via Excel</h3>
      <button class="modal__close" onclick="closeModal('modalImportFreq')" type="button">×</button>
    </div>
    <form method="POST" action="/frequencia/importar" enctype="multipart/form-data">
      <?= csrfField() ?>
      <input type="hidden" name="turma_id" value="<?= $turma['id'] ?>">
      <input type="hidden" name="disciplina_id" value="<?= $disciplina['id'] ?>">
      
      <div class="modal__body">
        <div class="form-group">
          <label class="form-label form-label--required">Arquivo Excel (.xlsx)</label>
          <input type="file" name="arquivo" class="form-control" accept=".xlsx,.xls" required>
          <span class="form-hint">
            Colunas: Nome do Aluno | Data da Aula | Presença (P/F)
          </span>
        </div>

        <div class="alert alert--info">
          <span class="alert__icon">💡</span>
          <div>
            <strong>Dica:</strong> Baixe a planilha modelo para facilitar.
            <br>
            <a href="/frequencia/modelo-excel?turma_id=<?= $turma['id'] ?>" class="btn btn--sm btn--ghost" style="margin-top:.5rem">
              📥 Baixar Modelo
            </a>
          </div>
        </div>
      </div>
      <div class="modal__footer">
        <button type="button" class="btn btn--ghost" onclick="closeModal('modalImportFreq')">Cancelar</button>
        <button type="submit" class="btn btn--primary">📤 Importar</button>
      </div>
    </form>
  </div>
</div>

<script>
function marcarTodos(checkbox) {
  const checkboxes = document.querySelectorAll('.check-presente');
  checkboxes.forEach(cb => {
    cb.checked = checkbox.checked;
    atualizarBadge(cb);
  });
  
  // Sincroniza os dois checkboxes de "marcar todos"
  document.getElementById('checkAll').checked = checkbox.checked;
  document.getElementById('checkAllHeader').checked = checkbox.checked;
}

// Atualiza badge de presença quando marca/desmarca
document.querySelectorAll('.check-presente').forEach(checkbox => {
  checkbox.addEventListener('change', function() {
    atualizarBadge(this);
  });
});

function atualizarBadge(checkbox) {
  const name = checkbox.getAttribute('name');
  const alunoId = name.match(/\[(\d+)\]/)[1];
  const badge = document.querySelector('.presenca-badge-' + alunoId);
  
  if (checkbox.checked) {
    badge.textContent = 'Presente';
    badge.className = 'badge badge--success presenca-badge-' + alunoId;
  } else {
    badge.textContent = 'Falta';
    badge.className = 'badge badge--danger presenca-badge-' + alunoId;
  }
}
</script>

<style>
.form-control--sm {
  padding: .4rem .75rem;
  font-size: .875rem;
}
</style>
