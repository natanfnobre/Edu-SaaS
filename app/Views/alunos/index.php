<div class="page-header">
  <div class="page-header__text">
    <h1>Alunos</h1>
    <p>Gerencie os alunos matriculados na escola</p>
  </div>
  <div class="page-header__actions">
    <?php if (can('alunos.criar')): ?>
      <a href="/alunos/novo" class="btn btn--primary">➕ Novo Aluno</a>
    <?php endif; ?>
  </div>
</div>

<!-- Busca -->
<div class="card" style="margin-bottom:1.5rem">
  <div class="card__body" style="padding:1rem">
    <form method="GET" action="/alunos">
      <div class="search-bar">
        <span class="search-bar__icon">🔍</span>
        <input 
          type="search" 
          name="busca" 
          placeholder="Buscar por nome, CPF ou RG..." 
          value="<?= e($busca) ?>"
          autofocus
        >
        <?php if ($busca): ?>
          <a href="/alunos" class="btn btn--sm btn--ghost" style="flex-shrink:0">Limpar</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php if (empty($alunos)): ?>

  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">👨‍🎓</div>
      <div class="empty-state__title">Nenhum aluno encontrado</div>
      <div class="empty-state__text">
        <?= $busca ? 'Tente outro termo de busca' : 'Comece cadastrando o primeiro aluno da escola' ?>
      </div>
      <?php if (!$busca && can('alunos.criar')): ?>
        <a href="/alunos/novo" class="btn btn--primary" style="margin-top:1rem">➕ Cadastrar Aluno</a>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>

  <!-- Lista -->
  <div class="card">
    <div class="card__body" style="padding:0">
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Aluno</th>
              <th>Data Nasc.</th>
              <th>CPF</th>
              <th>Status</th>
              <th style="text-align:right">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($alunos as $aluno): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:.75rem">
                    <div class="avatar">
                      <?php if ($aluno['foto_path']): ?>
                        <img src="/assets/uploads/<?= e($aluno['foto_path']) ?>" alt="">
                      <?php else: ?>
                        <?= initials($aluno['nome_completo']) ?>
                      <?php endif; ?>
                    </div>
                    <div>
                      <strong><?= e($aluno['nome_completo']) ?></strong>
                      <?php if ($aluno['necessidades_especiais']): ?>
                        <div style="font-size:.75rem;color:var(--text-muted)">⚠️ NEE</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?= $aluno['data_nascimento'] ? dateBr($aluno['data_nascimento']) : '--' ?></td>
                <td><?= $aluno['cpf'] ? maskCpf($aluno['cpf']) : '--' ?></td>
                <td>
                  <span class="badge badge--<?= $aluno['ativo'] ? 'success' : 'neutral' ?>">
                    <?= $aluno['ativo'] ? 'Ativo' : 'Inativo' ?>
                  </span>
                </td>
                <td style="text-align:right">
                  <div style="display:flex;gap:.4rem;justify-content:flex-end;flex-wrap:wrap">
                    <a href="/alunos/<?= $aluno['id'] ?>" class="btn btn--sm btn--ghost">Ver</a>
                    <?php if (can('alunos.editar')): ?>
                      <a href="/alunos/<?= $aluno['id'] ?>/editar" class="btn btn--sm btn--ghost">Editar</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Paginação -->
  <?php if ($paginacao['last_page'] > 1): ?>
    <div style="display:flex;justify-content:center;margin-top:1.5rem">
      <div class="pagination">
        <?php if ($paginacao['current_page'] > 1): ?>
          <a href="?page=<?= $paginacao['current_page'] - 1 ?><?= $busca ? '&busca=' . urlencode($busca) : '' ?>" class="page-item">‹</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
          <a 
            href="?page=<?= $i ?><?= $busca ? '&busca=' . urlencode($busca) : '' ?>" 
            class="page-item <?= $i === $paginacao['current_page'] ? 'page-item--active' : '' ?>"
          >
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <?php if ($paginacao['current_page'] < $paginacao['last_page']): ?>
          <a href="?page=<?= $paginacao['current_page'] + 1 ?><?= $busca ? '&busca=' . urlencode($busca) : '' ?>" class="page-item">›</a>
        <?php endif; ?>
      </div>
    </div>

    <p class="text-center text-muted mt-md">
      Página <?= $paginacao['current_page'] ?> de <?= $paginacao['last_page'] ?> 
      (<?= $paginacao['total'] ?> alunos no total)
    </p>
  <?php endif; ?>

<?php endif; ?>
