<div class="page-header">
  <div class="page-header__text">
    <h1>Turmas</h1>
    <p>Ano Letivo: <?= e($anoLetivo['nome']) ?></p>
  </div>
  <div class="page-header__actions">
    <?php if (can('turmas.gerenciar')): ?>
      <a href="/turmas/nova" class="btn btn--primary">➕ Nova Turma</a>
    <?php endif; ?>
  </div>
</div>

<?php if (empty($turmas)): ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">🏫</div>
      <div class="empty-state__title">Nenhuma turma cadastrada</div>
      <div class="empty-state__text">Crie a primeira turma para este ano letivo</div>
      <?php if (can('turmas.gerenciar')): ?>
        <a href="/turmas/nova" class="btn btn--primary" style="margin-top:1rem">➕ Criar Turma</a>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="grid grid-3">
    <?php foreach ($turmas as $turma): ?>
      <div class="card">
        <div class="card__header">
          <h3 class="card__title"><?= e($turma['nome']) ?></h3>
          <span class="badge badge--neutral" style="text-transform:capitalize"><?= e($turma['turno']) ?></span>
        </div>
        <div class="card__body">
          <p class="text-muted text-small" style="margin-bottom:.75rem"><?= e($turma['serie']) ?></p>
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a href="/turmas/<?= $turma['id'] ?>" class="btn btn--sm btn--ghost btn--full">Ver Detalhes</a>
            <?php if (can('turmas.gerenciar')): ?>
              <a href="/turmas/<?= $turma['id'] ?>/editar" class="btn btn--sm btn--ghost btn--full">Editar</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
