<div class="page-header">
  <div class="page-header__text">
    <h1><?= $aluno ? 'Editar Aluno' : 'Novo Aluno' ?></h1>
    <p><?= $aluno ? 'Atualize os dados do aluno' : 'Preencha os dados para cadastrar um novo aluno' ?></p>
  </div>
</div>

<form 
  method="POST" 
  action="<?= $aluno ? '/alunos/' . $aluno['id'] : '/alunos' ?>" 
  enctype="multipart/form-data"
  data-loading
>
  <?= csrfField() ?>
  <?php if ($aluno): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

  <div class="grid grid-2" style="gap:1.5rem">

    <!-- Dados Pessoais -->
    <div class="card">
      <div class="card__header">
        <h3 class="card__title">Dados Pessoais</h3>
      </div>
      <div class="card__body">

        <div class="form-group">
          <label class="form-label form-label--required" for="nome_completo">Nome Completo</label>
          <input 
            type="text" 
            id="nome_completo" 
            name="nome_completo" 
            class="form-control" 
            value="<?= e($aluno['nome_completo'] ?? '') ?>"
            required
            autofocus
          >
        </div>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="data_nascimento">Data de Nascimento</label>
            <input 
              type="date" 
              id="data_nascimento" 
              name="data_nascimento" 
              class="form-control"
              value="<?= e($aluno['data_nascimento'] ?? '') ?>"
            >
          </div>
          <div class="form-group">
            <label class="form-label" for="foto">Foto</label>
            <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
            <?php if (!empty($aluno['foto_path'])): ?>
              <span class="form-hint">Foto atual: <a href="/assets/uploads/<?= e($aluno['foto_path']) ?>" target="_blank">Ver</a></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="rg">RG</label>
            <input type="text" id="rg" name="rg" class="form-control" value="<?= e($aluno['rg'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="cpf">CPF</label>
            <input 
              type="text" 
              id="cpf" 
              name="cpf" 
              class="form-control" 
              value="<?= e(!empty($aluno['cpf']) ? maskCpf($aluno['cpf']) : '') ?>"
              placeholder="000.000.000-00"
            >
          </div>
        </div>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="naturalidade">Naturalidade</label>
            <input type="text" id="naturalidade" name="naturalidade" class="form-control" value="<?= e($aluno['naturalidade'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="nacionalidade">Nacionalidade</label>
            <input type="text" id="nacionalidade" name="nacionalidade" class="form-control" value="<?= e($aluno['nacionalidade'] ?? 'Brasileira') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="necessidades_especiais">Necessidades Especiais</label>
          <textarea id="necessidades_especiais" name="necessidades_especiais" class="form-control" rows="2"><?= e($aluno['necessidades_especiais'] ?? '') ?></textarea>
          <span class="form-hint">Descreva eventuais condições especiais ou necessidades de acompanhamento</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="observacoes_medicas">Observações Médicas</label>
          <textarea id="observacoes_medicas" name="observacoes_medicas" class="form-control" rows="2"><?= e($aluno['observacoes_medicas'] ?? '') ?></textarea>
          <span class="form-hint">Alergias, medicações contínuas, restrições, etc.</span>
        </div>

      </div>
    </div>

    <!-- Endereço -->
    <div class="card">
      <div class="card__header">
        <h3 class="card__title">Endereço</h3>
      </div>
      <div class="card__body">

        <div class="form-group">
          <label class="form-label" for="cep">CEP</label>
          <input type="text" id="cep" name="cep" class="form-control" value="<?= e(isset($aluno['endereco']) ? $aluno['endereco']['cep'] : '') ?>" placeholder="00000-000">
        </div>

        <div class="form-group">
          <label class="form-label" for="logradouro">Logradouro</label>
          <input type="text" id="logradouro" name="logradouro" class="form-control" value="<?= e(isset($aluno['endereco']) ? $aluno['endereco']['logradouro'] : '') ?>" placeholder="Rua, Avenida...">
        </div>

        <div class="form-row form-row--3">
          <div class="form-group">
            <label class="form-label" for="numero">Número</label>
            <input type="text" id="numero" name="numero" class="form-control" value="<?= e(isset($aluno['endereco']) ? $aluno['endereco']['numero'] : '') ?>">
          </div>
          <div class="form-group" style="grid-column: span 2">
            <label class="form-label" for="complemento">Complemento</label>
            <input type="text" id="complemento" name="complemento" class="form-control" value="<?= e(isset($aluno['endereco']['complemento']) ? $aluno['endereco']['complemento'] : '') ?>" placeholder="Apto, Bloco...">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="bairro">Bairro</label>
          <input type="text" id="bairro" name="bairro" class="form-control" value="<?= e(isset($aluno['endereco']) ? $aluno['endereco']['bairro'] : '') ?>">
        </div>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade" class="form-control" value="<?= e(isset($aluno['endereco']) ? $aluno['endereco']['cidade'] : '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="estado">Estado</label>
            <select id="estado" name="estado" class="form-control">
              <option value="">Selecione</option>
              <?php 
              $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
              foreach ($estados as $uf): ?>
                <option value="<?= $uf ?>" <?= (isset($aluno['endereco']) && $aluno['endereco']['estado'] === $uf) ? 'selected' : '' ?>><?= $uf ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

      </div>
    </div>

  </div>

  <!-- Ações -->
  <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;flex-wrap:wrap">
    <a href="<?= $aluno ? '/alunos/' . $aluno['id'] : '/alunos' ?>" class="btn btn--ghost">Cancelar</a>
    <button type="submit" class="btn btn--primary">
      <?= $aluno ? '✓ Salvar Alterações' : '➕ Cadastrar Aluno' ?>
    </button>
  </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara RG, CPF e CEP
    const inputCpf = document.getElementById('cpf');
    const inputCep = document.getElementById('cep');
    const inputRg = document.getElementById('rg');

    if(inputCpf) {
        inputCpf.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '-' + x[4] : '');
        });
    }

    if(inputRg) {
        inputRg.addEventListener('input', function(e) {
            // Regex Basico RG (Geralmente varia de estado)
            let v = e.target.value.replace(/\D/g, "");
            v = v.replace(/(\d{1,2})(\d{3})(\d{3})(\d{1})/, "$1.$2.$3-$4");
            e.target.value = v;
        });
    }

    if(inputCep) {
        inputCep.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,5})(\d{0,3})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
        });

        // ViaCEP Busca
        inputCep.addEventListener('blur', function(e) {
            let cepValue = this.value.replace(/\D/g, '');
            if(cepValue.length === 8) {
                // Loading indication
                this.classList.add('loading-field');
                
                fetch(`https://viacep.com.br/ws/${cepValue}/json/`)
                    .then(r => r.json())
                    .then(data => {
                        if(!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.localidade || '';
                            
                            // Seleciona o estado
                            let estadoSelect = document.getElementById('estado');
                            if(data.uf) {
                                Array.from(estadoSelect.options).forEach(opt => {
                                    if(opt.value === data.uf) opt.selected = true;
                                });
                            }
                            
                            document.getElementById('numero').focus(); // pula pro numero
                        }
                    })
                    .catch(console.error)
                    .finally(() => this.classList.remove('loading-field'));
            }
        });
    }
});
</script>
