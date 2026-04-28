<?php
// Agrupa vínculos por período
$porPeriodo = [];
foreach ($vinculos as $v) {
    $porPeriodo[$v['periodo_nome']][] = $v;
}
?>

<div class="page-header">
  <div class="page-header__text">
    <h1>Notas</h1>
    <p>Ano Letivo: <?= e($anoAtivo['nome']) ?></p>
  </div>
</div>

<?php if (empty($vinculos)): ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">📝</div>
      <div class="empty-state__title">Nenhuma turma vinculada</div>
      <div class="empty-state__text">Você não possui turmas ou disciplinas atribuídas neste ano letivo.</div>
    </div>
  </div>
<?php else: ?>

  <?php foreach ($porPeriodo as $periodoNome => $itens): ?>
    <div class="section-header" style="margin:1.5rem 0 .75rem">
      <h2 style="font-size:1rem;font-weight:600;color:var(--text-muted)"><?= e($periodoNome) ?></h2>
    </div>

    <div class="grid grid-3">
      <?php foreach ($itens as $v): ?>
        <div class="card card--hoverable <?= $v['notas_bloqueadas'] ? 'card--muted' : '' ?>">
          <div class="card__header">
            <div style="display:flex;align-items:center;gap:.5rem">
              <span style="width:12px;height:12px;border-radius:50%;background:<?= e($v['cor_icone']) ?>;flex-shrink:0;display:inline-block"></span>
              <h3 class="card__title" style="font-size:.95rem"><?= e($v['disciplina_nome']) ?></h3>
            </div>
            <?php if ($v['notas_bloqueadas']): ?>
              <span class="badge badge--danger" title="Período bloqueado pela coordenação">🔒</span>
            <?php elseif ($v['tem_notas']): ?>
              <span class="badge badge--success">✓ Lançado</span>
            <?php else: ?>
              <span class="badge badge--warning">Pendente</span>
            <?php endif; ?>
          </div>
          <div class="card__body">
            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.75rem">
              <?= e($v['turma_nome']) ?>
              <?php if ($v['serie'] ?? null): ?> · <?= e($v['serie']) ?><?php endif ?>
              · <?= str_replace('Manha', 'Manhã', ucfirst($v['turno'])) ?>
            </p>
            <a href="/notas/lancar/<?= $v['turma_id'] ?>/<?= $v['disciplina_id'] ?>/<?= $v['periodo_id'] ?>"
               class="btn btn--sm btn--primary btn--full <?= $v['notas_bloqueadas'] ? 'btn--disabled' : '' ?>">
              <?= $v['notas_bloqueadas'] ? '🔒 Período Bloqueado' : ($v['tem_notas'] ? '✏️ Editar Notas' : '📝 Lançar Notas') ?>
            </a>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  <?php endforeach ?>

<?php endif ?>
