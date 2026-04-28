<?php $pageTitle = 'Acesso de Responsável'; ?>

<!-- Cabeçalho mobile -->
<div class="auth-mobile-header">
  <div class="auth-mobile-header__icon">
    <?php if (!empty($tenant['visual']['logo_path'])): ?>
      <img src="/assets/uploads/<?= e($tenant['visual']['logo_path']) ?>" alt="Logo">
    <?php else: ?>
      👨‍👩‍👧
    <?php endif; ?>
  </div>
  <div class="auth-mobile-header__name"><?= e($tenant['nome'] ?? 'EduSaaS') ?></div>
  <div class="auth-mobile-header__sub">Portal do Responsável</div>
</div>

<h1 class="auth-title">Portal dos Pais 👨‍👩‍👧</h1>
<p class="auth-subtitle">Acompanhe o desempenho do seu filho</p>

<form method="POST" action="/pais/login" id="loginPaisForm" novalidate>
  <?= csrfField() ?>

  <div class="form-group">
    <label class="form-label form-label--required" for="identificador">
      CPF ou E-mail do responsável
    </label>
    <input
      type="text"
      id="identificador"
      name="identificador"
      class="form-control"
      placeholder="000.000.000-00 ou seu@email.com"
      autocomplete="username"
      inputmode="text"
      required
    >
    <span class="form-hint">Use o CPF cadastrado na escola ou seu e-mail</span>
  </div>

  <div class="form-group">
    <label class="form-label form-label--required" for="senha">Senha</label>
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
    <span class="form-hint">
      Senha padrão: últimos 4 dígitos do CPF + @<?= e(strtolower(str_replace(' ', '', $tenant['slug'] ?? 'escola'))) ?>
    </span>
  </div>

  <button type="submit" class="btn btn--primary btn--full btn--lg" id="btnLoginPais">
    Acessar portal
  </button>

</form>

<div class="auth-divider"><span>é da escola?</span></div>

<a href="/login" class="btn btn--ghost btn--full">
  🏫 Acessar como funcionário
</a>

<script>
function togglePassword(fieldId, btn) {
  const input = document.getElementById(fieldId);
  const isHidden = input.type === 'password';
  input.type   = isHidden ? 'text' : 'password';
  btn.textContent = isHidden ? '🙈' : '👁';
  input.focus();
}

document.getElementById('loginPaisForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnLoginPais');
  btn.classList.add('btn--loading');
  btn.disabled = true;
});
</script>
