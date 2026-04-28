<div class="page-header">
  <div class="page-header__text">
    <h1>Lançar Notas</h1>
    <p><?= e($turma['nome']) ?> › <?= e($disciplina['nome']) ?> › <?= e($periodo['nome']) ?></p>
  </div>
  <div class="page-header__actions">
    <button type="button" class="btn btn--ghost btn--sm" onclick="openModal('modalImport')">
      📤 Importar Excel
    </button>
  </div>
</div>

<form method="POST" action="/notas/salvar" id="formNotas" data-loading>
  <?= csrfField() ?>
  <input type="hidden" name="turma_id" value="<?= $turma['id'] ?>">
  <input type="hidden" name="disciplina_id" value="<?= $disciplina['id'] ?>">
  <input type="hidden" name="periodo_id" value="<?= $periodo['id'] ?>">

  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Alunos</h3>
      <span class="text-small text-muted"><?= count($alunos) ?> alunos</span>
    </div>
    <div class="card__body" style="padding:0">
      
      <div class="table-wrapper">
        <table class="table table--compact">
          <thead>
            <tr>
              <th style="min-width:180px">Aluno</th>
              <?php foreach ($avaliacoes as $av): ?>
                <th style="text-align:center;width:100px"><?= e($av['nome']) ?></th>
              <?php endforeach; ?>
              <th style="text-align:center;width:80px">Média</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($alunos as $aluno): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="avatar avatar--sm"><?= initials($aluno['nome_completo']) ?></div>
                    <span><?= e($aluno['nome_completo']) ?></span>
                  </div>
                </td>
                <?php 
                  $notasAluno = [];
                  foreach ($avaliacoes as $av):
                    $notaKey = "{$aluno['id']}-{$av['id']}";
                    $notaAtual = $notasExistentes[$notaKey] ?? null;
                    if ($notaAtual !== null) $notasAluno[] = (float)$notaAtual;
                ?>
                  <td style="text-align:center">
                    <input 
                      type="number" 
                      name="notas[<?= $aluno['id'] ?>][<?= $av['id'] ?>]"
                      class="nota-input"
                      value="<?= $notaAtual ? number_format($notaAtual, 1, ',', '') : '' ?>"
                      min="0" 
                      max="10"
                      step="0.1"
                      placeholder="--"
                      style="width:70px;text-align:center"
                    >
                  </td>
                <?php endforeach; ?>
                <td style="text-align:center">
                  <?php 
                    $media = !empty($notasAluno) ? round(array_sum($notasAluno) / count($notasAluno), 1) : null;
                    if ($media !== null):
                  ?>
                    <strong><?= number_format($media, 1, ',', '') ?></strong>
                  <?php else: ?>
                    <span class="text-muted">--</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
    
    <div class="card__footer">
      <div style="display:flex;gap:.75rem;justify-content:flex-end;flex-wrap:wrap">
        <a href="/notas" class="btn btn--ghost">Voltar</a>
        <button type="submit" class="btn btn--primary">✓ Salvar Notas</button>
      </div>
    </div>
  </div>
</form>
