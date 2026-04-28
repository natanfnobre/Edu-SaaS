<?php
// Conta quantos alunos já têm pelo menos uma nota neste período
$totalAlunos   = count($alunos);
$comNota       = 0;
foreach ($alunos as $al) {
    if (!empty($notasLancadas[$al['id']])) $comNota++;
}
$progresso = $totalAlunos > 0 ? round(($comNota / $totalAlunos) * 100) : 0;
?>

<!-- Header fixo com contexto -->
<div class="page-header" style="position:sticky;top:0;z-index:10;background:var(--bg);border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:0">
  <div class="page-header__text">
    <h1 style="font-size:1.1rem">
      <a href="/notas" style="color:var(--text-muted);text-decoration:none">Notas</a>
      <span style="color:var(--border)"> / </span>
      <?= e($turma['nome']) ?>
    </h1>
    <p style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
      <span style="display:inline-flex;align-items:center;gap:.3rem">
        <span style="width:10px;height:10px;border-radius:50%;background:<?= e($disciplina['cor_icone']) ?>"></span>
        <?= e($disciplina['nome']) ?>
      </span>
      <span style="color:var(--border)">·</span>
      <span><?= e($periodo['nome']) ?></span>
      <?php if ($bloqueado): ?>
        <span class="badge badge--danger">🔒 Bloqueado</span>
      <?php endif ?>
    </p>
  </div>
</div>

<!-- Barra de progresso -->
<div class="card" style="margin-bottom:1rem;padding:.75rem 1rem">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
    <span class="text-small text-muted">Progresso de lançamento</span>
    <strong style="font-size:.85rem"><?= $comNota ?>/<?= $totalAlunos ?> alunos</strong>
  </div>
  <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden">
    <div style="height:100%;width:<?= $progresso ?>%;background:var(--success);border-radius:3px;transition:width .3s"></div>
  </div>
</div>

<?php if ($bloqueado): ?>
  <div class="alert alert--warning" style="margin-bottom:1rem">
    🔒 <strong>Período bloqueado.</strong> O lançamento de notas está encerrado pela coordenação. 
    Para editar, solicite o desbloqueio.
  </div>
<?php endif ?>

<?php if (empty($avaliacoes)): ?>
  <div class="card"><div class="empty-state"><div class="empty-state__icon">⚙️</div>
    <div class="empty-state__title">Sem avaliações configuradas</div>
    <div class="empty-state__text">Não foi possível gerar as avaliações automáticas.</div>
  </div></div>
<?php elseif (empty($alunos)): ?>
  <div class="card"><div class="empty-state"><div class="empty-state__icon">👥</div>
    <div class="empty-state__title">Sem alunos matriculados</div>
    <div class="empty-state__text">Esta turma não possui alunos com matrícula ativa.</div>
  </div></div>
<?php else: ?>

<form method="POST" action="/notas/lancar" id="form-notas" data-loading>
  <?= csrfField() ?>
  <input type="hidden" name="turma_id"      value="<?= $turma['id'] ?>">
  <input type="hidden" name="disciplina_id" value="<?= $disciplina['id'] ?>">
  <input type="hidden" name="periodo_id"    value="<?= $periodo['id'] ?>">

  <!-- Desktop: tabela. Mobile: cards por aluno -->
  <div class="nota-grid">

    <!-- Cabeçalho (visível só em desktop) -->
    <div class="nota-grid__header">
      <div class="nota-grid__col-aluno">Aluno</div>
      <?php foreach ($avaliacoes as $av): ?>
        <div class="nota-grid__col-av" title="Peso: <?= $av['peso'] ?> · Máx: <?= $av['nota_maxima'] ?>">
          <?= e($av['nome']) ?>
          <small style="display:block;color:var(--text-muted);font-size:.7rem">Máx <?= rtrim(rtrim($av['nota_maxima'], '0'), '.') ?></small>
        </div>
      <?php endforeach ?>
      <div class="nota-grid__col-media">Média</div>
    </div>

    <!-- Linhas de alunos -->
    <?php foreach ($alunos as $aluno): ?>
      <?php
        $notasDoAluno = $notasLancadas[$aluno['id']] ?? [];
        // Calcula média atual (só para exibir)
        $notasParaMedia = [];
        foreach ($avaliacoes as $av) {
          $n = $notasDoAluno[$av['id']]['nota'] ?? null;
          $notasParaMedia[] = ['nota' => $n, 'peso' => $av['peso']];
        }
        $formula = 'simples'; // Usa simples para exibição inline
        $somaNotas = array_filter($notasParaMedia, fn($n) => $n['nota'] !== null);
        $mediaAtual = empty($somaNotas) ? null : round(array_sum(array_column($somaNotas, 'nota')) / count($somaNotas), 1);
      ?>
      <div class="nota-grid__row <?= isset($notasDoAluno) && !empty(array_filter($notasParaMedia, fn($n) => $n['nota'] !== null)) ? 'has-notas' : '' ?>">
        <div class="nota-grid__col-aluno">
          <div style="display:flex;align-items:center;gap:.5rem">
            <?php if ($aluno['foto_path']): ?>
              <img src="/assets/uploads/<?= e($aluno['foto_path']) ?>" class="avatar avatar--sm" alt="">
            <?php else: ?>
              <div class="avatar avatar--sm avatar--initials"><?= mb_strtoupper(mb_substr($aluno['nome_completo'], 0, 1)) ?></div>
            <?php endif ?>
            <span class="aluno-nome"><?= e($aluno['nome_completo']) ?></span>
          </div>
        </div>

        <?php foreach ($avaliacoes as $av): ?>
          <?php $notaAtual = $notasDoAluno[$av['id']]['nota'] ?? ''; ?>
          <div class="nota-grid__col-av">
            <label class="sr-only"><?= e($av['nome']) ?> — <?= e($aluno['nome_completo']) ?></label>
            <input
              type="text"
              inputmode="decimal"
              name="notas[<?= $av['id'] ?>][<?= $aluno['id'] ?>]"
              class="nota-input <?= $notaAtual !== '' && $notaAtual < 6 ? 'nota-input--baixa' : '' ?>"
              value="<?= $notaAtual !== '' ? number_format((float)$notaAtual, 1, ',', '') : '' ?>"
              placeholder="—"
              <?= $bloqueado ? 'disabled' : '' ?>
              data-max="<?= $av['nota_maxima'] ?>"
              oninput="atualizarMedia(this, <?= $aluno['id'] ?>)"
              autocomplete="off"
            >
          </div>
        <?php endforeach ?>

        <div class="nota-grid__col-media">
          <span class="media-display" id="media-<?= $aluno['id'] ?>">
            <?= $mediaAtual !== null ? number_format($mediaAtual, 1, ',', '') : '—' ?>
          </span>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <!-- Botão Salvar -->
  <?php if (!$bloqueado): ?>
    <div id="save-bar" style="margin-top:1.25rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
      <button type="submit" id="btn-salvar-notas" class="btn btn--primary" style="min-width:220px">
        💾 Salvar Todas as Notas
      </button>
      <a href="/notas" class="btn btn--outline">Cancelar</a>
    </div>
  <?php else: ?>
    <div style="margin-top:1rem">
      <a href="/notas" class="btn btn--outline">← Voltar</a>
    </div>
  <?php endif ?>
</form>

<?php endif ?>

<style>
/* ── Grade de Notas ────────────────────────────────────── */
.nota-grid { margin-top:1rem; overflow-x: auto; padding-bottom: .5rem; }

.nota-grid__header {
  display: grid;
  grid-template-columns: minmax(180px, 2fr) <?= str_repeat('minmax(72px, 85px) ', count($avaliacoes)) ?> 70px;
  gap: .5rem;
  padding: .5rem .75rem;
  background: var(--surface, var(--bg));
  border-radius: var(--radius-sm);
  font-size: .75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: .25rem;
}

.nota-grid__row {
  display: grid;
  grid-template-columns: minmax(180px, 2fr) <?= str_repeat('minmax(72px, 85px) ', count($avaliacoes)) ?> 70px;
  gap: .5rem;
  align-items: center;
  padding: .6rem .75rem;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
  transition: background .15s, border-color .15s;
}
.nota-grid__row:hover       { background: var(--surface, var(--bg)); }
.nota-grid__row.has-notas   { border-color: var(--border); }

.nota-grid__col-aluno  { display: flex; align-items: center; min-width: 0; }
.nota-grid__col-av,
.nota-grid__col-media  { display: flex; align-items: center; justify-content: center; text-align: center; }

.aluno-nome {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: .9rem;
}

.nota-input {
  width: 100%;
  max-width: 72px;
  text-align: center;
  padding: .4rem .3rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--bg);
  color: var(--text);
  font-size: .95rem;
  font-weight: 600;
  transition: border-color .15s, box-shadow .15s;
}
.nota-input:focus           { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-alpha); }
.nota-input--baixa          { color: var(--danger); border-color: var(--danger-alpha); }
.nota-input:disabled        { opacity: .5; cursor: not-allowed; }
/* Remove setas numéricas no Chrome */
.nota-input::-webkit-inner-spin-button,
.nota-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

.media-display {
  font-weight: 700;
  font-size: 1rem;
  color: var(--text-muted);
  min-width: 40px;
  text-align: center;
}

/* ── Mobile ────────────────────────────────────────────── */
@media (max-width: 600px) {
  .nota-grid__header { display: none; }

  .nota-grid__row {
    display: block;
    padding: .75rem;
    margin-bottom: .5rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
  }

  .nota-grid__col-aluno {
    font-weight: 600;
    margin-bottom: .75rem;
    font-size: .95rem;
  }

  .nota-grid__col-av,
  .nota-grid__col-media {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .4rem;
    justify-content: flex-start;
  }

  .nota-grid__col-av::before {
    content: attr(data-label);
    font-size: .75rem;
    color: var(--text-muted);
    min-width: 40px;
  }

  .nota-grid__col-media::before { content: "Média:"; font-size: .75rem; color: var(--text-muted); min-width: 40px; }

  .nota-input { max-width: 100px; }
}
</style>

<script>
// Atualiza a média em tempo real ao digitar
function atualizarMedia(input, alunoId) {
  const row    = input.closest('.nota-grid__row');
  const inputs = row.querySelectorAll('.nota-input');
  let soma = 0, count = 0;
  inputs.forEach(inp => {
    // Aceita tanto ponto quanto vírgula como separador decimal
    const raw = inp.value.replace(',', '.');
    const v   = parseFloat(raw);
    const max = parseFloat(inp.dataset.max) || 10;
    if (!isNaN(v) && v >= 0 && v <= max) {
      soma += v;
      count++;
      inp.classList.toggle('nota-input--baixa', v < 6);
    } else {
      inp.classList.remove('nota-input--baixa');
    }
  });
  const media  = count > 0 ? (soma / count).toFixed(1) : '—';
  const el     = document.getElementById('media-' + alunoId);
  if (el) {
    el.textContent = media !== '—' ? media.replace('.', ',') : '—';
    el.style.color = (count > 0 && (soma / count) < 6) ? 'var(--danger)' : 'var(--success)';
  }
}

// Normaliza separador decimal antes de submeter (vírgula → ponto)
document.getElementById('form-notas')?.addEventListener('submit', function(e) {
  this.querySelectorAll('.nota-input').forEach(inp => {
    if (inp.value) inp.value = inp.value.replace(',', '.');
  });
});

// Adiciona data-label nos cols de avaliação (para mobile)
document.querySelectorAll('.nota-grid__row').forEach(row => {
  const headers = document.querySelectorAll('.nota-grid__header > div');
  row.querySelectorAll('.nota-grid__col-av').forEach((col, i) => {
    col.dataset.label = headers[i + 1]?.textContent?.trim().split('\n')[0] ?? 'AV';
  });
});
</script>
