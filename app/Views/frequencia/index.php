<div class="page-header">
  <div class="page-header__text">
    <h1>Frequência</h1>
    <p>Ano Letivo: <?= e($anoAtivo['nome']) ?></p>
  </div>
</div>

<?php if (empty($vinculos)): ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">📋</div>
      <div class="empty-state__title">Nenhuma turma vinculada</div>
      <div class="empty-state__text">Você não possui turmas ou disciplinas atribuídas neste ano letivo.</div>
    </div>
  </div>
<?php else: ?>

  <div class="grid grid-3">
    <?php foreach ($vinculos as $v): ?>
      <div class="card card--hoverable">
        <div class="card__header">
          <div style="display:flex;align-items:center;gap:.5rem">
            <span style="width:12px;height:12px;border-radius:50%;background:<?= e($v['cor_icone']) ?>;display:inline-block;flex-shrink:0"></span>
            <h3 class="card__title" style="font-size:.95rem"><?= e($v['disciplina_nome']) ?></h3>
          </div>
        </div>
        <div class="card__body">
          <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem">
            <?= e($v['turma_nome']) ?> · <?= str_replace('Manha', 'Manhã', ucfirst($v['turno'])) ?>
          </p>
          <div class="stat-row" style="margin-bottom:.75rem">
            <span class="text-small text-muted">Aulas registradas</span>
            <strong><?= $v['total_aulas'] ?></strong>
          </div>
          <a href="/frequencia/lancar/<?= $v['turma_id'] ?>/<?= $v['disciplina_id'] ?>"
             class="btn btn--sm btn--primary btn--full">
            📋 Registrar Chamada
          </a>
        </div>
      </div>
    <?php endforeach ?>
  </div>

<?php endif ?>
