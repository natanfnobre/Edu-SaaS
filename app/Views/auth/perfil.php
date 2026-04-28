<div class="page-header">
  <div class="page-header__text">
    <h1>Meu Perfil</h1>
    <p>Ajuste suas preferências, altere sua senha e informações de conta.</p>
  </div>
</div>

<div class="grid grid-2" style="gap:1.5rem;">
  
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Informações Básicas</h3>
    </div>
    <div class="card__body">
       <form method="POST" action="/perfil">
          <?= csrfField() ?>
          <div class="form-group">
             <label class="form-label form-label--required">Sua Identificação (Nome)</label>
             <input type="text" name="nome_completo" class="form-control" value="<?= e(currentUser()['nome_completo'] ?? '') ?>" required>
          </div>
          
          <div class="form-group">
             <label class="form-label">E-mail de Acesso</label>
             <input type="email" class="form-control" value="<?= e(currentUser()['email'] ?? '') ?>" readonly style="background:#eee; cursor:not-allowed;">
             <span class="form-hint">O e-mail de acesso não pode ser alterado livremente para manter trilha de auditoria. Contate a base.</span>
          </div>

          <button type="submit" class="btn btn--primary">Salvar Perfil</button>
       </form>
    </div>
  </div>

  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Segurança (Alterar Senha)</h3>
    </div>
    <div class="card__body">
       <form method="POST" action="/perfil/senha">
          <?= csrfField() ?>
          <div class="form-group">
            <label class="form-label form-label--required">Senha Atual</label>
            <input type="password" name="senha_atual" class="form-control" required>
          </div>

          <div class="form-group">
            <label class="form-label form-label--required">Nova Senha</label>
            <input type="password" name="nova_senha" class="form-control" required>
            <span class="form-hint">Mínimo de 8 caracteres</span>
          </div>

          <button type="submit" class="btn btn--primary">Mudar Senha</button>
       </form>
    </div>
  </div>

  <div class="card" style="grid-column: span 2;">
    <div class="card__header">
      <h3 class="card__title">Preferências do Sistema</h3>
    </div>
    <div class="card__body">
       <form method="POST" action="/perfil/tema">
          <?= csrfField() ?>
          <div class="form-group">
             <label class="form-label">Tema da Interface (Em breve)</label>
             <div style="display:flex; gap:1rem;">
                <label style="display:flex; align-items:center; gap:0.5em;"><input type="radio" name="tema" value="claro" checked> Tema Claro Padrão</label>
                <label style="display:flex; align-items:center; gap:0.5em; opacity: 0.5;"><input type="radio" name="tema" value="escuro" disabled> Modo Escuro</label>
             </div>
          </div>
          <button type="submit" class="btn btn--outline">Gravar Preferência</button>
       </form>
    </div>
  </div>

</div>
