<!-- Header fixo com contexto -->
<div class="page-header" style="position:sticky;top:0;z-index:10;background:var(--bg);border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:0">
  <div class="page-header__text">
    <h1 style="font-size:1.1rem">
      <a href="/notas" style="color:var(--text-muted);text-decoration:none">Notas</a>
      <span style="color:var(--border)"> / </span>
      Recuperação
    </h1>
    <p style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
      <span style="display:inline-flex;align-items:center;gap:.3rem">
        <span style="width:10px;height:10px;border-radius:50%;background:<?= e($disciplina['cor_icone']) ?>"></span>
        <?= e($disciplina['nome']) ?>
      </span>
      <span style="color:var(--border)">·</span>
      <span><?= e($turma['nome']) ?></span>
      <span style="color:var(--border)">·</span>
      <strong><?= e($periodoRec['nome']) ?></strong>
      
      <?php if ($bloqueado): ?>
        <span class="badge badge--danger">🔒 Prazo Fechado</span>
      <?php endif ?>
    </p>
  </div>
</div>

<?php if ($bloqueado): ?>
  <div class="alert alert--warning" style="margin-top:1rem">
    🔒 <strong>Período bloqueado ou expirado.</strong> A coordenação encerrou as datas para aferição dessa recuperação!
  </div>
<?php endif ?>

<form method="POST" action="/recuperacao/lancar" id="form-rec" data-loading style="margin-top:1.5rem">
  <?= csrfField() ?>
  <input type="hidden" name="turma_id"      value="<?= $turma['id'] ?>">
  <input type="hidden" name="disciplina_id" value="<?= $disciplina['id'] ?>">
  <input type="hidden" name="periodo_rec_id" value="<?= $periodoRec['id'] ?>">

  <div class="nota-grid" style="overflow-x:auto;">
    <!-- Cabeçalho (Desktop) -->
    <div class="nota-grid__header" style="grid-template-columns: minmax(180px, 2fr) 90px 100px 90px;">
      <div class="nota-grid__col-aluno">Aluno</div>
      <div class="text-center" title="Média Oficial Antes da Recuperação">Nota Base</div>
      <div class="text-center">Nota Rec.</div>
      <div class="text-center">Status</div>
    </div>

    <!-- Linhas de alunos -->
    <?php foreach ($alunos as $aluno): ?>
      <?php
        // A lógica de verificação se ele pegou recuperação deve filtrar ou listar todos os alunos.
        // Simulando cálculo da base atual ou mostrando o que ele obteve 
        $notasDoAluno = $notasLancadasBase[$aluno['id']] ?? [];
        $notasParaMedia = [];
        foreach ($avaliacoes as $av) {
          $notasParaMedia[] = ['nota' => $notasDoAluno[$av['id']]['nota'] ?? null, 'peso' => $av['peso']];
        }
        $somaNotas = array_filter($notasParaMedia, fn($n) => $n['nota'] !== null);
        $mediaAtual = empty($somaNotas) ? null : round(array_sum(array_column($somaNotas, 'nota')) / count($somaNotas), 1);
        
        $recDoAluno = $notasJaAferidasRec[$aluno['id']] ?? null;
        $notaRecLançada = $recDoAluno ? $recDoAluno['nota'] : '';
        $teveSubstituicao = $recDoAluno ? $recDoAluno['nota_substituiu'] : false;
      ?>
      <div class="nota-grid__row" style="grid-template-columns: minmax(180px, 2fr) 90px 100px 90px;">
        <div class="nota-grid__col-aluno">
          <div style="display:flex;align-items:center;gap:.5rem">
            <?php if ($aluno['foto_path']): ?>
              <img src="/assets/uploads/<?= e($aluno['foto_path']) ?>" class="avatar avatar--sm" alt="">
            <?php else: ?>
              <div class="avatar avatar--sm avatar--initials"><?= mb_strtoupper(mb_substr($aluno['nome_completo'], 0, 1)) ?></div>
            <?php endif ?>
            <span class="aluno-nome"><?= e($aluno['nome_completo']) ?></span>
          </div>
        </div>

        <div class="text-center">
            <span style="font-weight:700; color:var(--text-muted); <?= $teveSubstituicao ? 'text-decoration:line-through; opacity:0.6;' : '' ?>">
              <?= $mediaAtual !== null ? number_format($mediaAtual, 1, ',', '') : '—' ?>
            </span>
        </div>

        <div class="text-center">
          <input
            type="text"
            inputmode="decimal"
            name="notas_rec[<?= $aluno['id'] ?>]"
            class="nota-input"
            value="<?= $notaRecLançada !== '' ? number_format((float)$notaRecLançada, 1, ',', '') : '' ?>"
            placeholder="—"
            <?= $bloqueado ? 'disabled' : '' ?>
            data-max="10"
            autocomplete="off"
            style="width:80px; max-width:100%"
          >
        </div>

        <div class="text-center">
             <?php if ($recDoAluno): ?>
                <?php if ($teveSubstituicao): ?>
                    <span class="badge badge--success" style="font-size:0.65rem">Maior</span>
                <?php else: ?>
                    <span class="badge badge--neutral" style="font-size:0.65rem">Menor/Igual</span>
                <?php endif; ?>
             <?php else: ?>
                —
             <?php endif; ?>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <?php if (!$bloqueado): ?>
    <div id="save-bar" style="margin-top:1.25rem;display:flex;gap:.75rem;align-items:center">
      <button type="submit" class="btn btn--primary" style="min-width:220px">💾 Salvar Recuperações</button>
      <a href="/notas" class="btn btn--outline">Voltar Geral</a>
    </div>
  <?php endif ?>
</form>

<script>
// Normaliza separador decimal (vírgula → ponto) ao submeter
document.getElementById('form-rec')?.addEventListener('submit', function(e) {
  this.querySelectorAll('.nota-input').forEach(inp => {
    if (inp.value) inp.value = inp.value.replace(',', '.');
  });
});
</script>
