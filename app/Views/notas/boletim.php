<?php
// Usa o serviço de badge
use App\Services\MediaService;
?>

<div class="page-header">
  <div class="page-header__text">
    <h1>Boletim Escolar</h1>
    <p><?= e($aluno['nome_completo']) ?></p>
  </div>
  <div class="page-header__actions">
    <a href="/alunos/<?= $aluno['id'] ?>" class="btn btn--outline btn--sm">← Ficha do Aluno</a>
  </div>
</div>

<?php if ($matricula): ?>
  <div class="card" style="margin-bottom:1rem;padding:.75rem 1rem">
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.85rem;color:var(--text-muted)">
      <span>🏫 Turma: <strong style="color:var(--text)"><?= e($matricula['turma_nome']) ?></strong></span>
      <span>📅 Matrícula: <strong style="color:var(--text)"><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></strong></span>
    </div>
  </div>
<?php endif ?>

<?php if (empty($boletim)): ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">📝</div>
      <div class="empty-state__title">Sem notas lançadas</div>
      <div class="empty-state__text">Ainda não há notas registradas para este aluno.</div>
    </div>
  </div>
<?php else: ?>

  <?php foreach ($boletim as $periodoNome => $disciplinas): ?>
    <div style="margin-bottom:1.5rem">
      <h2 style="font-size:.9rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.75rem">
        <?= e($periodoNome) ?>
      </h2>

      <div class="card">
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Disciplina</th>
                <th class="text-center" style="min-width:60px">AV1</th>
                <th class="text-center" style="min-width:60px">AV2</th>
                <th class="text-center" style="min-width:55px; color:var(--warning)">Rec.</th>
                <th class="text-center" style="min-width:70px;font-weight:700">Média</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($disciplinas as $discNome => $dados): ?>
                <?php
                  $badgeData = MediaService::badgeStatus($dados['status']);
                  $notas     = $dados['notas'];
                ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:.4rem">
                      <span style="width:8px;height:8px;border-radius:50%;background:<?= e($dados['cor_icone']) ?>;flex-shrink:0;display:inline-block"></span>
                      <?= e($discNome) ?>
                    </div>
                  </td>
                  <?php for ($i = 0; $i < 2; $i++): ?>
                    <td class="text-center" style="font-weight:600">
                      <?php
                        $nota = $notas[$i]['nota'] ?? null;
                        if ($nota !== null) {
                          $cor = $nota < 6 ? 'var(--danger)' : 'var(--success)';
                          echo "<span style='color:{$cor}'>".number_format((float)$nota,1,',','')."</span>";
                        } else {
                          echo "<span class='text-muted'>—</span>";
                        }
                      ?>
                    </td>
                  <?php endfor ?>
                  <td class="text-center" style="font-weight:600">
                    <?php if(isset($dados['nota_rec'])): ?>
                      <span style="<?= $dados['rec_substituiu'] ? 'color:var(--success); font-weight:700' : 'color:var(--text-muted)' ?>" title="Nota de Recuperação (<?= $dados['rec_substituiu'] ? 'Substituiu a Média' : 'Abaixo da Média: Não Aprovada' ?>)">
                        <?= number_format((float)$dados['nota_rec'], 1, ',', '') ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if (isset($dados['media_base'])): ?>
                      <span style="text-decoration:line-through; font-size:.8rem; color:var(--danger); margin-right:.25rem" title="Média Original">
                        <?= number_format($dados['media_base'], 1, ',', '') ?>
                      </span>
                    <?php endif ?>
                    <?php if ($dados['media'] !== null): ?>
                      <strong style="font-size:1.05rem;color:<?= $dados['media'] < 6 ? 'var(--danger)' : 'var(--success)' ?>">
                        <?= number_format($dados['media'], 1, ',', '') ?>
                      </strong>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif ?>
                  </td>
                  <td class="text-center">
                    <span class="badge <?= $badgeData['class'] ?>"><?= $badgeData['label'] ?></span>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endforeach ?>

<?php endif ?>
