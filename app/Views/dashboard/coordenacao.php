<div class="page-header">
  <div class="page-header__text">
    <h1>Dashboard da Escola</h1>
    <p>Visão geral do ano letivo atual</p>
  </div>
  <div class="page-header__actions">
    <a href="/relatorios" class="btn btn--ghost btn--sm">📊 Relatórios</a>
  </div>
</div>

<!-- Cards de estatísticas -->
<div class="grid grid-4" style="margin-bottom:2rem">

  <div class="stat-card">
    <div class="stat-card__icon" style="background:color-mix(in srgb, var(--color-primary) 15%, transparent);color:var(--color-primary)">
      👨‍🎓
    </div>
    <div>
      <div class="stat-card__value"><?= e($stats['total_alunos']) ?></div>
      <div class="stat-card__label">Alunos ativos</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card__icon" style="background:color-mix(in srgb, var(--success) 15%, transparent);color:var(--success)">
      🏫
    </div>
    <div>
      <div class="stat-card__value"><?= e($stats['total_turmas']) ?></div>
      <div class="stat-card__label">Turmas</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card__icon" style="background:color-mix(in srgb, var(--secondary) 15%, transparent);color:var(--secondary)">
      👨‍🏫
    </div>
    <div>
      <div class="stat-card__value"><?= e($stats['total_professores']) ?></div>
      <div class="stat-card__label">Professores</div>
    </div>
  </div>

  <a href="/relatorios/risco" class="stat-card stat-card--clickable" style="text-decoration:none; color:inherit; transition: transform 0.2s; cursor:pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
    <div class="stat-card__icon" style="background:color-mix(in srgb, var(--danger) 15%, transparent);color:var(--danger)">
      ⚠️
    </div>
    <div>
      <div class="stat-card__value"><?= e($stats['alunos_risco']) ?></div>
      <div class="stat-card__label">Alunos em risco</div>
      <div class="stat-card__trend text-muted" style="color:var(--danger)">Ver lista detalhada &rarr;</div>
    </div>
  </a>

</div>

<!-- Ações rápidas -->
<div class="grid grid-2">

  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Ações Rápidas</h3>
    </div>
    <div class="card__body">
      <div style="display:flex;flex-direction:column;gap:.75rem">
        <a href="/alunos/novo" class="btn btn--ghost" style="justify-content:flex-start">
          ➕ Cadastrar novo aluno
        </a>
        <a href="/turmas/nova" class="btn btn--ghost" style="justify-content:flex-start">
          🏫 Criar nova turma
        </a>
        <a href="/usuarios" class="btn btn--ghost" style="justify-content:flex-start">
          👥 Gerenciar usuários
        </a>
        <a href="/recuperacao" class="btn btn--ghost" style="justify-content:flex-start">
          🔄 Abrir período de recuperação
        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Comunicação</h3>
    </div>
    <div class="card__body">
      <div style="display:flex;flex-direction:column;gap:.75rem">
        <a href="/comunicados/novo" class="btn btn--ghost" style="justify-content:flex-start">
          📢 Publicar comunicado
        </a>
        <a href="/agenda" class="btn btn--ghost" style="justify-content:flex-start">
          📆 Criar evento na agenda
        </a>
        <a href="/relatorios/turma/1" class="btn btn--ghost" style="justify-content:flex-start">
          📊 Gerar relatório de turma
        </a>
      </div>
    </div>
  </div>

</div>

<div style="margin-top:2rem" class="card">
  <div class="card__header">
    <h3 class="card__title">Atividades Recentes</h3>
  </div>
  <div class="card__body">
    <div class="empty-state" style="padding:2rem 1rem">
      <div class="empty-state__icon">📋</div>
      <div class="empty-state__text">Nenhuma atividade recente registrada</div>
    </div>
  </div>
</div>
