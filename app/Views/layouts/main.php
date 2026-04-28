<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e(($_SESSION['tema'] === 'sistema' ? ($tenant['visual']['tema_padrao'] ?? 'claro') : ($_SESSION['tema'] ?? 'claro'))) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e($tenant['nome'] ?? 'EduSaaS') ?></title>

  <!-- CSS dinâmico do tenant -->
  <style><?= $cssVars ?? '' ?></style>

  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/layout.css">
  <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="/assets/css/<?= e($extraCss) ?>">
  <?php endif; ?>
</head>
<body>

<!-- Overlay para mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ── Sidebar ──────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">

  <!-- Logo / Nome da escola -->
  <div class="sidebar__logo">
    <?php if (!empty($tenant['visual']['logo_path'])): ?>
      <img src="/assets/uploads/<?= e($tenant['visual']['logo_path']) ?>" alt="Logo">
    <?php else: ?>
      <div class="avatar" style="border-radius:var(--radius-sm);font-size:1.1rem">📚</div>
    <?php endif; ?>
    <span class="sidebar__school-name"><?= e($tenant['nome'] ?? 'EduSaaS') ?></span>
  </div>

  <!-- Navegação -->
  <nav class="sidebar__nav" aria-label="Menu principal">

    <?php $role = $_SESSION['papel'] ?? ''; ?>

    <!-- Dashboard -->
    <div class="nav-section">
      <a href="/dashboard" class="nav-item <?= isActive('/dashboard', '/') ?>">
        <span class="nav-item__icon">🏠</span> Dashboard
      </a>
    </div>

    <?php if (in_array($role, ['professor', 'coordenador', 'diretor', 'super_admin'])): ?>
    <!-- Acadêmico -->
    <div class="nav-section">
      <div class="nav-section__label">Acadêmico</div>

      <?php if ($role === 'professor'): ?>
        <a href="/notas" class="nav-item <?= isActive('/notas') ?>">
          <span class="nav-item__icon">📝</span> Lançar Notas
        </a>
        <a href="/frequencia" class="nav-item <?= isActive('/frequencia') ?>">
          <span class="nav-item__icon">📅</span> Frequência
        </a>
      <?php endif; ?>

      <?php if (in_array($role, ['coordenador', 'diretor', 'super_admin'])): ?>
        <a href="/notas" class="nav-item <?= isActive('/notas') ?>">
          <span class="nav-item__icon">📝</span> Notas
        </a>
        <a href="/frequencia" class="nav-item <?= isActive('/frequencia') ?>">
          <span class="nav-item__icon">📅</span> Frequência
        </a>
        <a href="/recuperacao" class="nav-item <?= isActive('/recuperacao') ?>">
          <span class="nav-item__icon">🔄</span> Recuperação
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Alunos -->
    <?php if (can('alunos.ver')): ?>
    <div class="nav-section">
      <div class="nav-section__label">Alunos</div>
      <a href="/alunos" class="nav-item <?= isActive('/alunos') ?>">
        <span class="nav-item__icon">👨‍🎓</span> Alunos
      </a>
      <?php if (can('turmas.ver')): ?>
        <a href="/turmas" class="nav-item <?= isActive('/turmas') ?>">
          <span class="nav-item__icon">🏫</span> Turmas
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Comunicação -->
    <div class="nav-section">
      <div class="nav-section__label">Comunicação</div>
      <a href="/agenda" class="nav-item <?= isActive('/agenda') ?>">
        <span class="nav-item__icon">📆</span> Agenda
      </a>
      <a href="/comunicados" class="nav-item <?= isActive('/comunicados') ?>">
        <span class="nav-item__icon">📢</span> Comunicados
        <?php /* Badge dinâmico virá aqui */ ?>
      </a>
    </div>

    <!-- Relatórios -->
    <?php if (can('relatorios.boletim')): ?>
    <div class="nav-section">
      <div class="nav-section__label">Relatórios</div>
      <a href="/relatorios" class="nav-item <?= isActive('/relatorios') ?>">
        <span class="nav-item__icon">📊</span> Relatórios
      </a>
    </div>
    <?php endif; ?>

    <!-- Admin -->
    <?php if (in_array($role, ['diretor', 'super_admin', 'coordenador'])): ?>
    <div class="nav-section">
      <div class="nav-section__label">Administração</div>
      <a href="/usuarios" class="nav-item <?= isActive('/usuarios') ?>">
        <span class="nav-item__icon">👥</span> Usuários
      </a>
      <a href="/configuracoes" class="nav-item <?= isActive('/configuracoes') ?>">
        <span class="nav-item__icon">⚙️</span> Configurações
      </a>
      <?php if (can('auditoria.ver')): ?>
        <a href="/auditoria" class="nav-item <?= isActive('/auditoria') ?>">
          <span class="nav-item__icon">🔍</span> Auditoria
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </nav>

  <!-- User card -->
  <div class="sidebar__user" onclick="window.location='/perfil'">
    <div class="avatar">
      <?php $user = currentUser(); ?>
      <?php if (!empty($user['foto_path'])): ?>
        <img src="/assets/uploads/<?= e($user['foto_path']) ?>" alt="">
      <?php else: ?>
        <?= initials($user['nome'] ?? 'U') ?>
      <?php endif; ?>
    </div>
    <div class="sidebar__user-info">
      <div class="sidebar__user-name"><?= e($user['nome'] ?? '') ?></div>
      <div class="sidebar__user-role"><?= e(roleName($_SESSION['papel'] ?? '')) ?></div>
    </div>
    <span style="font-size:.9rem;color:var(--text-light)">›</span>
  </div>

</aside>

<!-- ── Navbar ────────────────────────────────────────────────── -->
<header class="navbar no-print">

  <!-- Hamburger (mobile) -->
  <button class="navbar__hamburger" onclick="toggleSidebar()" aria-label="Menu" type="button">☰</button>

  <!-- Logo mobile -->
  <div class="navbar__logo-mobile">
    <?php if (!empty($tenant['visual']['logo_path'])): ?>
      <img src="/assets/uploads/<?= e($tenant['visual']['logo_path']) ?>" alt="">
    <?php endif; ?>
    <span><?= e($tenant['nome'] ?? 'EduSaaS') ?></span>
  </div>

  <!-- Título da página (desktop) -->
  <span class="navbar__title"><?= e($pageTitle ?? '') ?></span>

  <!-- Actions -->
  <div class="navbar__actions">

    <!-- Toggle tema -->
    <button class="navbar__icon-btn" onclick="toggleTheme()" title="Alternar tema" type="button" id="btnTheme">
      🌙
    </button>

    <!-- Notificações -->
    <button class="navbar__icon-btn" title="Notificações" type="button">
      🔔
      <span class="navbar__notif-dot" id="notifDot" style="display:none"></span>
    </button>

    <!-- Sair -->
    <a href="/logout" class="navbar__icon-btn" title="Sair">🚪</a>

  </div>
</header>

<!-- ── Conteúdo principal ────────────────────────────────────── -->
<main class="main-content">

  <!-- Flash messages -->
  <?php foreach (\App\Helpers\Flash::all() as $type => $messages): ?>
    <?php foreach ($messages as $msg): ?>
      <div class="alert alert--<?= e($type === 'error' ? 'error' : ($type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : 'info'))) ?>">
        <span class="alert__icon">
          <?= match($type) { 'success' => '✅', 'error' => '⚠️', 'warning' => '⚡', default => 'ℹ️' } ?>
        </span>
        <span><?= e($msg) ?></span>
        <button class="alert__close" onclick="this.parentElement.remove()" type="button">×</button>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>

  <?= $content ?>

</main>

<!-- ── Bottom nav (mobile) ──────────────────────────────────── -->
<nav class="bottom-nav no-print">
  <a href="/dashboard"    class="bottom-nav__item <?= isActive('/dashboard', '/') ?>">
    <span class="bottom-nav__item__icon">🏠</span>Início
  </a>
  <?php if ($role === 'professor'): ?>
    <a href="/notas"      class="bottom-nav__item <?= isActive('/notas') ?>">
      <span class="bottom-nav__item__icon">📝</span>Notas
    </a>
    <a href="/frequencia" class="bottom-nav__item <?= isActive('/frequencia') ?>">
      <span class="bottom-nav__item__icon">📅</span>Freq.
    </a>
  <?php else: ?>
    <a href="/alunos"     class="bottom-nav__item <?= isActive('/alunos') ?>">
      <span class="bottom-nav__item__icon">👨‍🎓</span>Alunos
    </a>
    <a href="/turmas"     class="bottom-nav__item <?= isActive('/turmas') ?>">
      <span class="bottom-nav__item__icon">🏫</span>Turmas
    </a>
  <?php endif; ?>
  <a href="/comunicados"  class="bottom-nav__item <?= isActive('/comunicados') ?>">
    <span class="bottom-nav__item__icon">📢</span>Avisos
  </a>
  <a href="/perfil"       class="bottom-nav__item <?= isActive('/perfil') ?>">
    <span class="bottom-nav__item__icon">👤</span>Perfil
  </a>
</nav>

<!-- ── Scripts ───────────────────────────────────────────────── -->
<script src="/assets/js/app.js"></script>

</body>
</html>
