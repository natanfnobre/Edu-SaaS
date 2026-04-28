<?php $pageTitle = 'Entrar'; ?>

<!-- Cabeçalho mobile -->
<div class="auth-mobile-header">
  <div class="auth-mobile-header__icon">
    <?php if (!empty($tenant['visual']['logo_path'])): ?>
      <img src="/assets/uploads/<?= e($tenant['visual']['logo_path']) ?>" alt="Logo">
    <?php else: ?>
      📚
    <?php endif; ?>
  </div>
  <div class="auth-mobile-header__name"><?= e($tenant['nome'] ?? 'EduSaaS') ?></div>
  <div class="auth-mobile-header__sub">Sistema de Gestão Escolar</div>
</div>

<h1 class="auth-title">Bem-vindo de volta 👋</h1>
<p class="auth-subtitle">Acesse sua conta para continuar</p>

<form method="POST" action="/login" id="loginForm" novalidate>
  <?= csrfField() ?>

  <div class="form-group">
    <label class="form-label form-label--required" for="email">E-mail</label>
    <input
      type="email"
      id="email"
      name="email"
      class="form-control"
      placeholder="seu@email.com"
      autocomplete="email"
      inputmode="email"
      required
      value="<?= e($_POST['email'] ?? '') ?>"
    >
  </div>

  <div class="form-group">
    <div class="form-group--flex">
      <label class="form-label form-label--required" for="senha">Senha</label>
      <a href="/recuperar-senha" class="forgot-link">Esqueci a senha</a>
    </div>
    <div class="input-password-wrapper">
      <input
        type="password"
        id="senha"
        name="senha"
        class="form-control"
        placeholder="••••••••"
        autocomplete="current-password"
        required
      >
      <button type="button" class="btn-show-pass" aria-label="Mostrar senha" onclick="togglePassword('senha', this)">
        👁
      </button>
    </div>
  </div>

  <div class="form-group mt-sm">
    <label class="form-check">
      <input type="checkbox" name="lembrar" value="1">
      <span style="font-size:.875rem;color:var(--text-muted)">Lembrar-me por 30 dias</span>
    </label>
  </div>

  <button type="submit" class="btn btn--primary btn--full btn--lg" id="btnLogin">
    Entrar no sistema
  </button>

</form>

<div class="auth-divider"><span>ou</span></div>

<a href="/pais/login" class="btn btn--ghost btn--full">
  👨‍👩‍👧 Acessar como pai / responsável
</a>

<script>
function togglePassword(fieldId, btn) {
  const input = document.getElementById(fieldId);
  const isHidden = input.type === 'password';
  input.type   = isHidden ? 'text' : 'password';
  btn.textContent = isHidden ? '🙈' : '👁';
  input.focus();
}

// Feedback de loading no submit
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnLogin');
  btn.classList.add('btn--loading');
  btn.disabled = true;
});
</script>
