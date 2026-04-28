<?php $pageTitle = $pageTitle ?? 'Comunicados'; ?>

<div class="card">
  <div class="card__header"><h3 class="card__title">Comunicados</h3></div>
  <div class="card__body">
    <?php if (empty($comunicados)): ?>
      <p class="text-muted">Nenhum comunicado disponível.</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:.75rem">
        <?php foreach ($comunicados as $c): ?>
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
            <div>
              <strong><?= e($c['titulo']) ?></strong>
              <div style="color:var(--text-muted);font-size:.9rem;margin-top:.25rem"><?= e(truncate($c['conteudo'] ?? '', 200)) ?></div>
              <div style="margin-top:.5rem;color:var(--text-muted);font-size:.85rem">Publicado em <?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.5rem;align-items:flex-end">
              <?php if ((int)$c['lido_por_mim'] > 0): ?>
                <span class="badge badge--success">Lido</span>
              <?php else: ?>
                <form method="POST" action="/pais/comunicados/<?= $c['id'] ?>/ler" onsubmit="return confirm('Confirmar leitura deste comunicado?');">
                  <?= csrfField() ?>
                  <button class="btn btn--primary btn--sm">Confirmar leitura</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <hr style="border:none;border-top:1px solid var(--border);margin:.5rem 0">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
