<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($tenant['visual']['tema_padrao'] ?? 'claro') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($tenant['nome'] ?? 'EduSaaS') ?> — <?= $pageTitle ?? 'Entrar' ?></title>
  <meta name="robots" content="noindex">

  <!-- CSS dinâmico do tenant -->
  <style><?= $cssVars ?? '' ?></style>

  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="auth-body">

  <div class="auth-wrapper">

    <!-- Painel visual (esquerda no desktop) -->
    <div class="auth-panel">
      <div class="auth-panel__inner">
        <div class="auth-panel__logo">
          <?php if (!empty($tenant['visual']['logo_path'])): ?>
            <img src="/assets/uploads/<?= e($tenant['visual']['logo_path']) ?>" alt="Logo">
          <?php else: ?>
            <div class="auth-panel__logo-placeholder">📚</div>
          <?php endif; ?>
        </div>
        <h1 class="auth-panel__title"><?= e($tenant['nome'] ?? 'EduSaaS') ?></h1>
        <p class="auth-panel__subtitle">Sistema de Gestão Escolar</p>
        <div class="auth-panel__features">
          <div class="auth-feature"><span>✓</span> Lançamento de notas e frequência</div>
          <div class="auth-feature"><span>✓</span> Diário de anotações por aluno</div>
          <div class="auth-feature"><span>✓</span> Portal para pais e responsáveis</div>
          <div class="auth-feature"><span>✓</span> Boletins e relatórios em PDF</div>
        </div>
      </div>
    </div>

    <!-- Formulário (direita) -->
    <div class="auth-form-area">
      <div class="auth-form-card">

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

      </div>
    </div>

  </div>

</body>
</html>
