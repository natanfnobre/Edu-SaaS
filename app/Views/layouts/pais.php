<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($tenant['visual']['tema_padrao'] ?? 'claro') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($tenant['nome'] ?? 'EduSaaS') ?> — <?= $pageTitle ?? 'Portal dos Pais' ?></title>

  <!-- CSS dinâmico do tenant -->
  <style><?= $cssVars ?? '' ?></style>

  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/layout.css">
</head>
<body>

  <header style="background:var(--bg);border-bottom:1px solid var(--border);padding:.75rem 1rem;position:sticky;top:0;z-index:50">
    <div style="max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:1rem">
      <div style="display:flex;align-items:center;gap:.75rem">
        <?php if (!empty($tenant['visual']['logo_path'])): ?>
          <img src="/assets/uploads/<?= e($tenant['visual']['logo_path']) ?>" alt="Logo" style="height:34px;object-fit:contain">
        <?php else: ?>
          <div style="font-size:1.2rem">🏫</div>
        <?php endif; ?>
        <div style="font-weight:700"><?= e($tenant['nome'] ?? 'EduSaaS') ?></div>
      </div>

      <nav style="margin-left:auto;display:flex;gap:.5rem">
        <a href="/pais" class="btn btn--ghost">Início</a>
        <a href="/pais/boletim" class="btn btn--ghost">Boletim</a>
        <a href="/pais/frequencia" class="btn btn--ghost">Frequência</a>
        <a href="/pais/comunicados" class="btn btn--ghost">Comunicados</a>
        <a href="/pais/logout" class="btn btn--secondary">Sair</a>
      </nav>
    </div>
  </header>

  <main style="max-width:1100px;margin:1.25rem auto;padding:0 1rem">

    <?php // Flash messages
    foreach (\App\Helpers\Flash::all() as $type => $messages):
      foreach ($messages as $msg): ?>
        <div class="alert alert--<?= e($type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'info')) ?>">
          <span class="alert__icon"><?= $type === 'error' ? '⚠️' : '✅' ?></span>
          <?= e($msg) ?>
        </div>
      <?php endforeach;
    endforeach; ?>

    <?= $content ?? '' ?>

  </main>

</body>
</html>
