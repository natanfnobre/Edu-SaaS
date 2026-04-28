<div class="page-header">
  <div class="page-header__text">
    <div style="display:flex; align-items:center; gap: 1rem;">
      <h1><?= e($turma['nome']) ?></h1>
      <?php if ($turma['ativo']): ?>
          <span class="badge badge--success">Ativa</span>
      <?php else: ?>
          <span class="badge badge--neutral">Inativa</span>
      <?php endif; ?>
    </div>
    <p><?= e($turma['serie']) ?> · Período Letivo: <?= e($turma['ano_letivo']['nome'] ?? 'Vigente') ?></p>
  </div>
  <div class="page-header__actions">
    <a href="/turmas/<?= $turma['id'] ?>/editar" class="btn btn--secondary">✏️ Editar Turma</a>
    <a href="/turmas" class="btn btn--outline">Voltar</a>
  </div>
</div>

<div class="grid grid-2" style="gap:1.5rem">
  
  <!-- Coluna: Alunos / Matrículas -->
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Alunos Matriculados (<?= count($alunos) ?>)</h3>
    </div>
    <div class="card__body" style="padding:0">
      <?php if (empty($alunos)): ?>
        <div class="empty-state">
           <div class="empty-state__icon">🎒</div>
           <div class="empty-state__title">Turma Vazia</div>
           <div class="empty-state__text">Nenhum aluno está matriculado nesta turma ainda.</div>
        </div>
      <?php else: ?>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Situação</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($alunos as $aluno): ?>
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:.5rem">
                     <?php if ($aluno['foto_path']): ?>
                        <img src="/assets/uploads/<?= e($aluno['foto_path']) ?>" class="avatar avatar--sm" alt="">
                     <?php else: ?>
                        <div class="avatar avatar--sm avatar--initials"><?= mb_substr($aluno['nome_completo'], 0, 1) ?></div>
                     <?php endif; ?>
                     <strong><?= e($aluno['nome_completo']) ?></strong>
                  </div>
                </td>
                 <td>
                  <?php if (($aluno['status'] ?? '') === 'ativo'): ?>
                    <span class="badge badge--success">Matriculado</span>
                  <?php else: ?>
                    <span class="badge badge--warning">Transferido/Inativo</span>
                  <?php endif; ?>
                 </td>
                <td>
                   <a href="/alunos/<?= $aluno['id'] ?>" class="btn btn--sm btn--ghost">Perfil</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <?php if (can('alunos.editar')): ?>
        <div style="padding:1rem;border-top:1px solid var(--border);">
          <form method="POST" action="/turmas/<?= $turma['id'] ?>" style="display:flex;gap:.5rem;align-items:center">
            <?= csrfField() ?>
            <input type="hidden" name="acao" value="adicionar_aluno">
            <select name="aluno_id" class="form-control" required style="flex:1">
              <option value="">Selecione um aluno para matricular</option>
              <?php foreach ($todosAlunos as $a): ?>
                <option value="<?= (int)$a['id'] ?>"><?= e($a['nome_completo']) ?> <?= $a['cpf'] ? ' — ' . maskCpf($a['cpf']) : '' ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn--primary" type="submit">Matricular</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Coluna: Grade Curricular e Docentes -->
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Professores e Disciplinas</h3>
    </div>
    <div class="card__body" style="padding:0">
        <div class="empty-state">
           <div class="empty-state__icon">📖</div>
           <div class="empty-state__title">Acesso Restrito</div>
           <div class="empty-state__text">Como a Coordenação define a Grade Horária, verifique o painel do docente para atribuições da turma <?= e($turma['nome']) ?>.</div>
        </div>
    </div>
  </div>

</div>
