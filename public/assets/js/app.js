/* app.js — Scripts globais do EduSaaS */

'use strict';

// ── Sidebar ───────────────────────────────────────────────────────

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.toggle('is-open');
  overlay.classList.toggle('is-open');
  document.body.style.overflow = sidebar.classList.contains('is-open') ? 'hidden' : '';
}

function closeSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.remove('is-open');
  overlay.classList.remove('is-open');
  document.body.style.overflow = '';
}

// Fecha sidebar com ESC
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeSidebar();
});

// ── Tema (claro / escuro) ─────────────────────────────────────────

function toggleTheme() {
  const html    = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next    = current === 'escuro' ? 'claro' : 'escuro';

  html.setAttribute('data-theme', next);
  updateThemeIcon(next);

  // Persiste no servidor
  fetch('/perfil/tema', {
    method:  'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body:    `tema=${next}&_csrf_token=${getMetaCsrf()}`,
  }).catch(() => {});

  // Persiste localmente (fallback)
  localStorage.setItem('edusaas_tema', next);
}

function updateThemeIcon(theme) {
  const btn = document.getElementById('btnTheme');
  if (btn) btn.textContent = theme === 'escuro' ? '☀️' : '🌙';
}

// Aplica tema salvo imediatamente
(function () {
  const saved = localStorage.getItem('edusaas_tema');
  const html  = document.documentElement;
  if (saved && saved !== html.getAttribute('data-theme')) {
    html.setAttribute('data-theme', saved);
  }
  updateThemeIcon(html.getAttribute('data-theme') || 'claro');
})();

// ── CSRF ──────────────────────────────────────────────────────────

function getMetaCsrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

// ── Alerts auto-dismiss ───────────────────────────────────────────

document.querySelectorAll('.alert').forEach(alert => {
  setTimeout(() => {
    alert.style.transition = 'opacity .4s, transform .4s';
    alert.style.opacity    = '0';
    alert.style.transform  = 'translateY(-8px)';
    setTimeout(() => alert.remove(), 400);
  }, 5000);
});

// ── Confirmação de ações destrutivas ─────────────────────────────

document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', function (e) {
    const msg = this.getAttribute('data-confirm') || 'Tem certeza?';
    if (!confirm(msg)) e.preventDefault();
  });
});

// ── Submit de formulários com loading ────────────────────────────

document.querySelectorAll('form[data-loading]').forEach(form => {
  form.addEventListener('submit', () => {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
      btn.classList.add('btn--loading');
      btn.disabled = true;
    }
  });
});

// ── Tooltips simples ──────────────────────────────────────────────

document.querySelectorAll('[title]').forEach(el => {
  // Evita tooltip nativo no mobile
  if ('ontouchstart' in window) el.removeAttribute('title');
});

// ── Nota input: valida range ──────────────────────────────────────

document.querySelectorAll('.nota-input').forEach(input => {
  const max = parseFloat(input.getAttribute('max') || '10');
  const min = parseFloat(input.getAttribute('min') || '0');

  input.addEventListener('blur', function () {
    const val = parseFloat(this.value.replace(',', '.'));
    if (isNaN(val)) { this.value = ''; return; }
    if (val < min)  this.value = min.toFixed(1);
    if (val > max)  this.value = max.toFixed(1);
    // Arredonda para 1 casa decimal
    this.value = parseFloat(this.value).toFixed(1);
    this.value = this.value.replace('.', ',');
  });

  input.addEventListener('focus', function () {
    this.select();
  });

  // Navega para próximo campo com Enter/Tab
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const inputs = Array.from(document.querySelectorAll('.nota-input'));
      const idx    = inputs.indexOf(this);
      if (idx < inputs.length - 1) inputs[idx + 1].focus();
    }
  });
});

// ── Frequência: marcar/desmarcar todos ───────────────────────────

const checkAll = document.getElementById('checkAll');
if (checkAll) {
  checkAll.addEventListener('change', function () {
    document.querySelectorAll('.check-presente').forEach(cb => {
      cb.checked = this.checked;
    });
  });
}

// ── Modal genérico ────────────────────────────────────────────────

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    modal.querySelector('.modal, input, button:not(.modal__close)')?.focus();
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}

// Fecha modal clicando no backdrop
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
  backdrop.addEventListener('click', function (e) {
    if (e.target === this) closeModal(this.id);
  });
});
