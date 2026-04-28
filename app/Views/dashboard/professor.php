<div class="page-header">
  <div class="page-header__text">
    <h1>Olá, <?= e(explode(' ', currentUser()['nome'])[0]) ?> 👋</h1>
    <p>
      <?php if ($anoAtivo ?? null): ?>
        Ano Letivo: <strong><?= e($anoAtivo['nome']) ?></strong>
      <?php else: ?>
        <span style="color:var(--warning)">⚠️ Nenhum ano letivo ativo — acesse <a href="/configuracoes">Configurações</a></span>
      <?php endif ?>
    </p>
  </div>
  <div class="page-header__actions">
    <a href="/notas" class="btn btn--primary btn--sm">📝 Lançar Notas</a>
    <a href="/frequencia" class="btn btn--outline btn--sm">📅 Frequência</a>
  </div>
</div>

<?php if (empty($stats['minhas_turmas'])): ?>

  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">📚</div>
      <div class="empty-state__title">Nenhuma turma atribuída</div>
      <div class="empty-state__text">
        Você ainda não possui turmas vinculadas neste ano letivo. Entre em contato com a coordenação.
      </div>
    </div>
  </div>

<?php else: ?>

  <!-- Cards rápidos -->
  <div class="grid grid-3" style="margin-bottom:2rem">

    <div class="stat-card">
      <div class="stat-card__icon" style="background:color-mix(in srgb, var(--color-primary) 15%, transparent);color:var(--color-primary)">
        🏫
      </div>
      <div>
        <div class="stat-card__value"><?= count($stats['minhas_turmas']) ?></div>
        <div class="stat-card__label">Turmas</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card__icon" style="background:color-mix(in srgb, var(--warning) 15%, transparent);color:var(--warning)">
        📝
      </div>
      <div>
        <div class="stat-card__value"><?= $stats['pendencias_notas'] ?></div>
        <div class="stat-card__label">Pendências de lançamento</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card__icon" style="background:color-mix(in srgb, var(--danger) 15%, transparent);color:var(--danger)">
        ⚠️
      </div>
      <div>
        <div class="stat-card__value"><?= $stats['alunos_atencao'] ?></div>
        <div class="stat-card__label">Alunos com atenção</div>
      </div>
    </div>

  </div>

  <!-- Lista de turmas -->
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Minhas Turmas</h3>
    </div>
    <div class="card__body" style="padding:0">
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Turma</th>
              <th>Série</th>
              <th>Turno</th>
              <th>Alunos</th>
              <th style="text-align:right">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stats['minhas_turmas'] as $turma): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="avatar avatar--sm" style="background:var(--color-primary)">
                      <?= mb_substr($turma['nome'], 0, 2) ?>
                    </div>
                    <strong><?= e($turma['nome']) ?></strong>
                  </div>
                </td>
                <td><?= e($turma['serie']) ?></td>
                <td>
                  <span class="badge badge--neutral" style="text-transform:capitalize">
                    <?= str_replace('manha', 'manhã', e($turma['turno'])) ?>
                  </span>
                </td>
                <td><?= e($turma['total_alunos']) ?> alunos</td>
                <td style="text-align:right">
                  <div style="display:flex;gap:.4rem;justify-content:flex-end;flex-wrap:wrap">
                    <a href="/notas?turma=<?= $turma['id'] ?>" class="btn btn--sm btn--ghost">
                      📝 Notas
                    </a>
                    <a href="/frequencia/lancar/<?= $turma['id'] ?>/1" class="btn btn--sm btn--ghost">
                      📅 Frequência
                    </a>
                    <a href="/turmas/<?= $turma['id'] ?>" class="btn btn--sm btn--ghost">
                      Ver
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php endif; ?>
