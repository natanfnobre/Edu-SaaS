<?php $pageTitle = $pageTitle ?? 'Portal dos Pais'; ?>

<div style="display:grid;grid-template-columns:1fr;gap:1rem">
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Resumo do Aluno</h3>
    </div>
    <div class="card__body">
      <?php if (empty($aluno)): ?>
        <p class="text-muted">Nenhum aluno vinculado.</p>
      <?php else: ?>
        <div style="display:flex;gap:1rem;align-items:center">
          <?php if (!empty($aluno['foto_path'])): ?>
            <img src="/assets/uploads/<?= e($aluno['foto_path']) ?>" alt="Foto" style="width:72px;height:72px;border-radius:8px;object-fit:cover">
          <?php endif; ?>
          <div>
            <h4 style="margin:0;"><?= e($aluno['nome_completo']) ?></h4>
            <div style="color:var(--text-muted)">CPF: <?= $aluno['cpf'] ? maskCpf($aluno['cpf']) : '--' ?></div>
            <div style="margin-top:.5rem">
              <a href="/notas/boletim/<?= $aluno['id'] ?>" class="btn btn--secondary btn--sm">Ver Boletim</a>
              <a href="/pais/diario" class="btn btn--ghost btn--sm">Diário</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card__header"><h3 class="card__title">Comunicados</h3></div>
    <div class="card__body">
      <p class="text-muted">Comunicados recentes e confirmações de leitura aparecerão aqui.</p>
      <a href="/pais/comunicados" class="btn btn--ghost btn--sm">Ver comunicados</a>
    </div>
  </div>

</div>
