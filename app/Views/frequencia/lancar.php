<?php
// Conta quantos estão marcados como presentes por padrão (todos)
$totalAlunos = count($alunos);
?>

<!-- Header fixo -->
<div class="page-header" style="position:sticky;top:0;z-index:10;background:var(--bg);border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:0">
  <div class="page-header__text">
    <h1 style="font-size:1.1rem">
      <a href="/frequencia" style="color:var(--text-muted);text-decoration:none">Frequência</a>
      <span style="color:var(--border)"> / </span>
      <?= e($turma['nome']) ?>
    </h1>
    <p style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
      <span style="display:inline-flex;align-items:center;gap:.3rem">
        <span style="width:10px;height:10px;border-radius:50%;background:<?= e($disciplina['cor_icone']) ?>"></span>
        <?= e($disciplina['nome']) ?>
      </span>
      <span style="color:var(--border)">·</span>
      <span><?= $totalAlunos ?> alunos</span>
    </p>
  </div>
</div>

<!-- Formulário de Nova Chamada -->
<form method="POST" action="/frequencia/lancar" id="form-frequencia" data-loading>
  <?= csrfField() ?>
  <input type="hidden" name="turma_id"      value="<?= $turma['id'] ?>">
  <input type="hidden" name="disciplina_id" value="<?= $disciplina['id'] ?>">
  <!-- Campo oculto com todos os IDs dos alunos para detectar ausentes -->
  <input type="hidden" name="todos_alunos"  value="<?= implode(',', array_column($alunos, 'id')) ?>">

  <!-- Configuração da Aula -->
  <div class="card" style="margin-top:1rem;margin-bottom:1rem">
    <div class="card__header">
      <h3 class="card__title">📅 Dados da Aula</h3>
    </div>
    <div class="card__body">
      <div class="form-row form-row--3">
        <div class="form-group">
          <label class="form-label form-label--required" for="data-aula">Data</label>
          <input type="date" id="data-aula" name="data" class="form-control"
                 value="<?= $hojeStr ?>" max="<?= $hojeStr ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="num-aulas">Nº de Aulas</label>
          <input type="number" id="num-aulas" name="numero_aulas" class="form-control"
                 value="1" min="1" max="8">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="conteudo">Conteúdo dado na aula <span class="text-muted">(opcional)</span></label>
        <textarea id="conteudo" name="conteudo_dado" class="form-control"
                  rows="2" placeholder="Descreva brevemente o conteúdo ministrado..."></textarea>
      </div>
    </div>
  </div>

  <!-- Controles da Chamada -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;flex-wrap:wrap;gap:.5rem">
    <h2 style="font-size:1rem;font-weight:600">Lista de Chamada</h2>
    <div style="display:flex;gap:.5rem">
      <button type="button" class="btn btn--sm btn--outline" onclick="marcarTodos(true)">✅ Marcar Todos</button>
      <button type="button" class="btn btn--sm btn--outline" onclick="marcarTodos(false)">❌ Desmarcar Todos</button>
    </div>
  </div>

  <!-- Contador de presentes -->
  <div class="card" style="margin-bottom:.75rem;padding:.6rem 1rem;display:flex;justify-content:space-between;align-items:center">
    <span class="text-small text-muted">Presentes</span>
    <div style="display:flex;gap:1rem">
      <strong style="color:var(--success)" id="contador-presentes"><?= $totalAlunos ?></strong>
      <span class="text-muted">/</span>
      <span><?= $totalAlunos ?></span>
      <span style="color:var(--danger);font-size:.85rem" id="contador-faltas">0 faltas</span>
    </div>
  </div>

  <!-- Lista de alunos -->
  <div class="chamada-lista">
    <?php foreach ($alunos as $aluno): ?>
      <?php
        $pctFaltas = $aluno['percentual_faltas'];
        $statusFreq = $pctFaltas >= 25 ? 'critico' : ($pctFaltas >= 18.75 ? 'atencao' : 'ok');
        $corStatus  = ['critico' => 'var(--danger)', 'atencao' => 'var(--warning)', 'ok' => 'var(--success)'];
      ?>
      <label class="chamada-item" for="aluno-<?= $aluno['id'] ?>">
        <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0">
          <?php if ($aluno['foto_path']): ?>
            <img src="/assets/uploads/<?= e($aluno['foto_path']) ?>" class="avatar avatar--md" alt="">
          <?php else: ?>
            <div class="avatar avatar--md avatar--initials">
              <?= mb_strtoupper(mb_substr($aluno['nome_completo'], 0, 1)) ?>
            </div>
          <?php endif ?>
          <div style="min-width:0">
            <div class="aluno-nome" style="font-weight:600;font-size:.95rem"><?= e($aluno['nome_completo']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted);display:flex;gap:.5rem;align-items:center">
              <span><?= $aluno['total_faltas'] ?> falta<?= $aluno['total_faltas'] !== 1 ? 's' : '' ?> de <?= $aluno['total_aulas'] ?> aulas</span>
              <?php if ($aluno['total_aulas'] > 0): ?>
                <span style="color:<?= $corStatus[$statusFreq] ?>;font-weight:600"><?= $pctFaltas ?>%</span>
              <?php endif ?>
            </div>
          </div>
        </div>

        <!-- Toggle Presente/Falta -->
        <div class="chamada-toggle">
          <input type="checkbox"
                 id="aluno-<?= $aluno['id'] ?>"
                 name="presentes[]"
                 value="<?= $aluno['id'] ?>"
                 class="chamada-checkbox"
                 checked
                 onchange="atualizarContador()">
          <div class="chamada-toggle__track">
            <span class="chamada-toggle__presente">✅ Presente</span>
            <span class="chamada-toggle__falta">❌ Falta</span>
          </div>
        </div>
      </label>
    <?php endforeach ?>
  </div>

  <!-- Botão Salvar Fixo -->
  <div style="position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--border);margin-top:1rem;display:flex;gap:.75rem;align-items:center">
    <button type="submit" class="btn btn--primary btn--full" style="max-width:320px">
      💾 Registrar Chamada
    </button>
    <a href="/frequencia" class="btn btn--outline">Cancelar</a>
  </div>
</form>

<!-- Histórico de aulas anteriores -->
<?php if (!empty($aulas)): ?>
<div style="margin-top:2rem">
  <h2 style="font-size:1rem;font-weight:600;color:var(--text-muted);margin-bottom:.75rem">Aulas Anteriores</h2>
  <div class="card">
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Período</th>
            <th class="text-center">Aulas</th>
            <th class="text-center">Presentes</th>
            <th class="text-center">Faltas</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($aulas as $aula): ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($aula['data'])) ?></td>
              <td class="text-muted text-small"><?= e($aula['periodo_nome']) ?></td>
              <td class="text-center"><?= $aula['numero_aulas'] ?></td>
              <td class="text-center" style="color:var(--success);font-weight:600"><?= $aula['total_presentes'] ?? 0 ?></td>
              <td class="text-center" style="color:var(--danger);font-weight:600"><?= $aula['total_faltas'] ?? 0 ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif ?>

<style>
/* ── Chamada Lista ────────────────────────────────────── */
.chamada-lista { 
  display: grid; 
  grid-template-columns: 1fr; 
  gap: .5rem; 
  padding-bottom: 6rem; /* Previne ocultação pelo botão sticky bottom */
}
@media (min-width: 768px) {
  .chamada-lista { grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1rem; }
}

.chamada-item {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .75rem 1rem;
  background: var(--surface);
  border-radius: var(--radius);
  border: 2px solid transparent;
  cursor: pointer;
  transition: border-color .15s, background .15s;
  -webkit-tap-highlight-color: transparent;
}
.chamada-item:has(.chamada-checkbox:checked)   { border-color: var(--success-alpha); background: var(--success-bg, rgba(34,197,94,.06)); }
.chamada-item:has(.chamada-checkbox:not(:checked)) { border-color: var(--danger-alpha); background: var(--danger-bg, rgba(239,68,68,.06)); }

/* Toggle visual */
.chamada-checkbox { display: none; }

.chamada-toggle { flex-shrink: 0; }

.chamada-toggle__track {
  display: flex;
  border-radius: 999px;
  border: 1.5px solid var(--border);
  overflow: hidden;
  font-size: .78rem;
  font-weight: 600;
}

.chamada-toggle__presente,
.chamada-toggle__falta {
  padding: .35rem .6rem;
  transition: background .15s, color .15s;
  white-space: nowrap;
}

/* Quando marcado (presente) */
.chamada-checkbox:checked ~ .chamada-toggle__track .chamada-toggle__presente {
  background: var(--success);
  color: #fff;
}
.chamada-checkbox:checked ~ .chamada-toggle__track .chamada-toggle__falta {
  background: transparent;
  color: var(--text-muted);
}

/* Quando desmarcado (falta) */
.chamada-checkbox:not(:checked) ~ .chamada-toggle__track .chamada-toggle__falta {
  background: var(--danger);
  color: #fff;
}
.chamada-checkbox:not(:checked) ~ .chamada-toggle__track .chamada-toggle__presente {
  background: transparent;
  color: var(--text-muted);
}

/* Aplica dentro do label com has() */
.chamada-item:has(.chamada-checkbox:checked) .chamada-toggle__presente { background: var(--success); color: #fff; }
.chamada-item:has(.chamada-checkbox:checked) .chamada-toggle__falta    { background: transparent; color: var(--text-muted); }
.chamada-item:has(.chamada-checkbox:not(:checked)) .chamada-toggle__falta     { background: var(--danger); color: #fff; }
.chamada-item:has(.chamada-checkbox:not(:checked)) .chamada-toggle__presente  { background: transparent; color: var(--text-muted); }

.aluno-nome { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

@media (max-width: 480px) {
  .chamada-toggle__presente::after { content: ""; }
  .chamada-toggle__falta::after    { content: ""; }
  .chamada-toggle__presente { padding: .35rem .5rem; }
  .chamada-toggle__falta    { padding: .35rem .5rem; }
}
</style>

<script>
function marcarTodos(presente) {
  document.querySelectorAll('.chamada-checkbox').forEach(cb => {
    cb.checked = presente;
    // Dispara change para atualizar estilos via :has()
    cb.dispatchEvent(new Event('change', { bubbles: true }));
  });
  atualizarContador();
}

function atualizarContador() {
  const total    = document.querySelectorAll('.chamada-checkbox').length;
  const presentes = document.querySelectorAll('.chamada-checkbox:checked').length;
  const faltas   = total - presentes;
  document.getElementById('contador-presentes').textContent = presentes;
  document.getElementById('contador-faltas').textContent    = faltas + (faltas === 1 ? ' falta' : ' faltas');
}
</script>
