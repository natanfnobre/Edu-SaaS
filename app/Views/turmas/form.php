<div class="page-header">
  <div class="page-header__text">
    <h1><?= isset($turma) ? 'Editar Turma' : 'Nova Turma' ?></h1>
    <p><?= isset($turma) ? 'Edite os dados desta turma no sistema.' : 'Crie uma nova turma para o ano letivo ativo.' ?></p>
  </div>
</div>

<form method="POST" action="<?= isset($turma) ? '/turmas/'.$turma['id'] : '/turmas' ?>" class="card">
  <?= csrfField() ?>
  
  <div class="card__body">
    <div class="form-row form-row--2">
      <div class="form-group">
        <label class="form-label form-label--required">Ano Letivo</label>
        <select name="ano_letivo_id" class="form-control" required>
          <option value="">Selecione...</option>
          <?php foreach ($anosLetivos as $ano): ?>
            <option value="<?= $ano['id'] ?>" <?= (isset($turma) && $turma['ano_letivo_id'] == $ano['id']) ? 'selected' : '' ?>>
              <?= date('Y', strtotime($ano['data_inicio'])) ?> - <?= $ano['nome'] ?? 'Período Regulamentar' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label form-label--required">Nome da Turma</label>
        <input type="text" name="nome" class="form-control" value="<?= e($turma['nome'] ?? '') ?>" placeholder="Ex: 9º Ano A" required>
      </div>

      <div class="form-group">
        <label class="form-label form-label--required">Série / Grau</label>
        <input type="text" name="serie" class="form-control" value="<?= e($turma['serie'] ?? '') ?>" placeholder="Ex: 9º Ano, Ensino Médio" required>
      </div>
    </div>

    <div class="form-row form-row--2">
      <div class="form-group">
        <label class="form-label form-label--required">Turno</label>
        <select name="turno" class="form-control" required>
          <option value="">Selecione...</option>
          <option value="manha" <?= (isset($turma) && $turma['turno'] === 'manha') ? 'selected' : '' ?>>Manhã</option>
          <option value="tarde" <?= (isset($turma) && $turma['turno'] === 'tarde') ? 'selected' : '' ?>>Tarde</option>
          <option value="noite" <?= (isset($turma) && $turma['turno'] === 'noite') ? 'selected' : '' ?>>Noite</option>
          <option value="integral" <?= (isset($turma) && $turma['turno'] === 'integral') ? 'selected' : '' ?>>Integral</option>
        </select>
      </div>
      
      <div class="form-group" style="display:flex; flex-direction:column; justify-content:center; padding-top: 2rem;">
         <label style="display:flex; align-items:center; gap:.5rem">
            <input type="checkbox" name="ativo" value="1" <?= (!isset($turma) || (isset($turma) && $turma['ativo'])) ? 'checked' : '' ?>> 
            Turma Ativa (Lecionando atualmente)
         </label>
      </div>
    </div>

    <div style="margin-top:2rem; display:flex; gap:1rem">
      <button type="submit" class="btn btn--primary"><?= isset($turma) ? 'Salvar Edição' : 'Criar Turma' ?></button>
      <a href="/turmas" class="btn btn--ghost">Cancelar</a>
    </div>
  </div>
</form>
