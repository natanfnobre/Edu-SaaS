<?php $pageTitle = $pageTitle ?? 'Boletim'; ?>

<div class="card">
  <div class="card__header"><h3 class="card__title">Boletim</h3></div>
  <div class="card__body">
    <?php if (empty($aluno)): ?>
      <p class="text-muted">Aluno não encontrado.</p>
    <?php else: ?>
      <?php
        // Busca matrícula ativa
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare('SELECT turma_id, ano_letivo_id FROM matriculas WHERE aluno_id = ? AND tenant_id = ? AND status = "ativo" LIMIT 1');
        $stmt->execute([$aluno['id'], tenantId()]);
        $mat = $stmt->fetch();
      ?>

      <?php if (!$mat): ?>
        <p class="text-muted">Aluno sem matrícula ativa.</p>
      <?php else: ?>
        <?php
          $notaModel = new \App\Models\Nota();
          $notas = $notaModel->porAlunoETurma($aluno['id'], (int)$mat['turma_id'], tenantId());
          if (empty($notas)) {
            echo '<p class="text-muted">Nenhuma nota registrada ainda.</p>'; 
          } else {
            // Agrupa por disciplina e período
            $group = [];
            foreach ($notas as $n) {
              $periodo = $n['periodo_nome'] . ' (' . $n['periodo_numero'] . ')';
              $disc = $n['disciplina_nome'];
              $group[$periodo][$disc][] = $n;
            }
            foreach ($group as $periodo => $td): ?>
              <h4 style="margin-top:1rem"><?= e($periodo) ?></h4>
              <div class="table-wrapper">
                <table class="table table--compact">
                  <thead><tr><th>Disciplina</th><th>Avaliação</th><th>Nota</th></tr></thead>
                  <tbody>
                  <?php foreach ($td as $disc => $rows):
                    foreach ($rows as $r): ?>
                      <tr>
                        <td><?= e($r['disciplina_nome']) ?></td>
                        <td><?= e($r['avaliacao_nome']) ?> (peso <?= e($r['peso']) ?>)</td>
                        <td><?= $r['nota'] !== null ? e($r['nota']) : '--' ?></td>
                      </tr>
                    <?php endforeach;
                  endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endforeach;
          }
        ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php $pageTitle = $pageTitle ?? 'Boletim'; ?>

<div class="card">
  <div class="card__header"><h3 class="card__title">Boletim</h3></div>
  <div class="card__body">
    <p class="text-muted">Visualização do boletim do aluno (em breve).</p>
  </div>
</div>
