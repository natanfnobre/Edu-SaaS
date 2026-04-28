<div class="page-header">
  <div class="page-header__text">
    <h1>Configurações da Escola</h1>
    <p><?= e($tenant['nome']) ?></p>
  </div>
</div>

<div class="tabs" style="margin-bottom:1.5rem">
  <div class="tab-item active" onclick="showTab('visual')">Visual</div>
  <div class="tab-item" onclick="showTab('academico')">Acadêmico</div>
  <div class="tab-item" onclick="showTab('anos')">Anos Letivos</div>
  <div class="tab-item" onclick="showTab('disciplinas')">Disciplinas</div>
</div>

<!-- Tab: Visual -->
<div id="tab-visual" class="tab-content">
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Identidade Visual</h3>
    </div>
    <div class="card__body">
      <form method="POST" action="/configuracoes/visual" enctype="multipart/form-data" data-loading>
        <?= csrfField() ?>

        <div class="form-group">
          <label class="form-label" for="logo">Logo da Escola</label>
          <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
          <?php if ($visual && $visual['logo_path']): ?>
            <div style="margin-top:.75rem">
              <img src="/assets/uploads/<?= e($visual['logo_path']) ?>" alt="Logo" style="width:80px;height:80px;border-radius:var(--radius-sm);object-fit:contain;border:1px solid var(--border)">
            </div>
          <?php endif; ?>
        </div>

        <div class="form-row form-row--3">
          <div class="form-group">
            <label class="form-label" for="cor_primaria">Cor Primária</label>
            <input type="color" id="cor_primaria" name="cor_primaria" class="form-control" value="<?= e($visual['cor_primaria'] ?? '#1e40af') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="cor_secundaria">Cor Secundária</label>
            <input type="color" id="cor_secundaria" name="cor_secundaria" class="form-control" value="<?= e($visual['cor_secundaria'] ?? '#3b82f6') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="cor_texto">Cor do Texto</label>
            <input type="color" id="cor_texto" name="cor_texto" class="form-control" value="<?= e($visual['cor_texto'] ?? '#ffffff') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="tema_padrao">Tema Padrão</label>
          <select id="tema_padrao" name="tema_padrao" class="form-control">
            <option value="claro" <?= ($visual['tema_padrao'] ?? 'claro') === 'claro' ? 'selected' : '' ?>>Claro</option>
            <option value="escuro" <?= ($visual['tema_padrao'] ?? 'claro') === 'escuro' ? 'selected' : '' ?>>Escuro</option>
          </select>
        </div>

        <button type="submit" class="btn btn--primary">✓ Salvar Configurações Visuais</button>
      </form>
    </div>
  </div>
</div>

<!-- Tab: Acadêmico -->
<div id="tab-academico" class="tab-content" style="display:none">
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Configurações Acadêmicas</h3>
    </div>
    <div class="card__body">
      <form method="POST" action="/configuracoes/academico" data-loading>
        <?= csrfField() ?>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label form-label--required" for="tipo_periodo">Tipo de Período</label>
            <select id="tipo_periodo" name="tipo_periodo" class="form-control" required>
              <option value="bimestre" <?= ($academico['tipo_periodo'] ?? 'bimestre') === 'bimestre' ? 'selected' : '' ?>>Bimestre</option>
              <option value="trimestre" <?= ($academico['tipo_periodo'] ?? 'bimestre') === 'trimestre' ? 'selected' : '' ?>>Trimestre</option>
              <option value="semestre" <?= ($academico['tipo_periodo'] ?? 'bimestre') === 'semestre' ? 'selected' : '' ?>>Semestre</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label form-label--required" for="qtd_periodos">Quantidade de Períodos</label>
            <input type="number" id="qtd_periodos" name="qtd_periodos" class="form-control" value="<?= e($academico['qtd_periodos'] ?? 4) ?>" min="1" max="12" required>
          </div>
        </div>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label form-label--required" for="qtd_avaliacoes_por_periodo">Avaliações por Período</label>
            <input type="number" id="qtd_avaliacoes_por_periodo" name="qtd_avaliacoes_por_periodo" class="form-control" value="<?= e($academico['qtd_avaliacoes_por_periodo'] ?? 2) ?>" min="1" max="10" required>
            <span class="form-hint">Ex: 2 = AV1 e AV2</span>
          </div>
          <div class="form-group">
            <label class="form-label form-label--required" for="formula_media">Fórmula de Média</label>
            <select id="formula_media" name="formula_media" class="form-control" required>
              <option value="simples" <?= ($academico['formula_media'] ?? 'simples') === 'simples' ? 'selected' : '' ?>>Simples (média aritmética)</option>
              <option value="ponderada" <?= ($academico['formula_media'] ?? 'simples') === 'ponderada' ? 'selected' : '' ?>>Ponderada (por peso)</option>
            </select>
          </div>
        </div>

        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label form-label--required" for="nota_minima_aprovacao">Nota Mínima de Aprovação</label>
            <input type="number" id="nota_minima_aprovacao" name="nota_minima_aprovacao" class="form-control" value="<?= e($academico['nota_minima_aprovacao'] ?? 6.0) ?>" min="0" max="10" step="0.1" required>
          </div>
          <div class="form-group">
            <label class="form-label form-label--required" for="percentual_maximo_faltas">% Máximo de Faltas</label>
            <input type="number" id="percentual_maximo_faltas" name="percentual_maximo_faltas" class="form-control" value="<?= e($academico['percentual_maximo_faltas'] ?? 25) ?>" min="0" max="100" required>
            <span class="form-hint">Ex: 25 = aluno reprovado com mais de 25% de faltas</span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="recuperacao_automatica" value="1" <?= !empty($academico['recuperacao_automatica']) ? 'checked' : '' ?>>
            <span>Substituição automática de nota na recuperação (se maior que a anterior)</span>
          </label>
        </div>

        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="plano_aula_habilitado" value="1" <?= !empty($academico['plano_aula_habilitado']) ? 'checked' : '' ?>>
            <span>Habilitar registro de plano de aula (opcional)</span>
          </label>
        </div>

        <button type="submit" class="btn btn--primary">✓ Salvar Configurações Acadêmicas</button>
      </form>
    </div>
  </div>
</div>

<!-- Tab: Anos Letivos -->
<div id="tab-anos" class="tab-content" style="display:none">
  <div class="card">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
      <h3 class="card__title">Anos Letivos</h3>
      <button class="btn btn--sm btn--primary" onclick="openModal('modal-ano')">+ Novo Ano Letivo</button>
    </div>
    <div class="card__body">
      <?php if (empty($anosLetivos)): ?>
        <p class="text-muted">Nenhum ano letivo cadastrado.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Início</th>
              <th>Fim</th>
              <th>Status</th>
              <th class="text-right">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($anosLetivos as $ano): ?>
              <tr>
                <td><strong><?= e($ano['nome']) ?></strong></td>
                <td><?= date('d/m/Y', strtotime($ano['data_inicio'])) ?></td>
                <td><?= date('d/m/Y', strtotime($ano['data_fim'])) ?></td>
                <td>
                  <?php if ($ano['ativo']): ?>
                    <span class="badge badge--success">Ativo</span>
                  <?php else: ?>
                    <span class="badge badge--warning">Inativo</span>
                  <?php endif; ?>
                </td>
                <td class="text-right">
                  <?php if (!$ano['ativo']): ?>
                    <form method="POST" action="/configuracoes/anos-letivos/<?= $ano['id'] ?>/ativar" style="display:inline">
                      <?= csrfField() ?>
                      <button type="submit" class="btn btn--sm btn--outline" title="Tornar este o ano ativo da escola">Ativar</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Tab: Disciplinas -->
<div id="tab-disciplinas" class="tab-content" style="display:none">
  <div class="card">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
      <h3 class="card__title">Disciplinas</h3>
      <button id="btn-nova-disciplina" class="btn btn--sm btn--primary" onclick="openModal('modal-disciplina')">+ Nova Disciplina</button>
    </div>
    <div class="card__body">
      <?php if (empty($disciplinas)): ?>
        <p class="text-muted">Nenhuma disciplina cadastrada.</p>
      <?php else: ?>
        <div style="display:grid;gap:.75rem">
          <?php foreach ($disciplinas as $disc): ?>
            <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem;border-radius:var(--radius-sm);border:1px solid var(--border)">
              <div style="width:24px;height:24px;border-radius:4px;background:<?= e($disc['cor_icone']) ?>;flex-shrink:0"></div>
              <div>
                <strong><?= e($disc['nome']) ?></strong><br>
                <span class="text-muted text-small"><?= e($disc['carga_horaria_semanal'] ?? 0) ?>h semanais</span>
              </div>
              <div style="margin-left:auto;display:flex;gap:.5rem">
                <button class="btn btn--sm btn--outline" onclick='editDisciplina(<?= json_encode($disc) ?>)'>Editar</button>
                <form method="POST" action="/configuracoes/disciplinas/<?= $disc['id'] ?>/deletar" onsubmit="return confirm('Tem certeza?')">
                   <?= csrfField() ?>
                   <button type="submit" class="btn btn--sm btn--danger">Excluir</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal: Novo Ano Letivo -->
<div id="modal-ano" class="modal" style="display:none">
  <div class="modal__content">
    <div class="modal__header">
      <h3>Novo Ano Letivo</h3>
      <button class="modal__close" onclick="closeModal('modal-ano')">&times;</button>
    </div>
    <form method="POST" action="/configuracoes/anos-letivos">
      <?= csrfField() ?>
      <div class="modal__body">
        <div class="form-group">
          <label class="form-label">Nome (ex: Ano Letivo 2026)</label>
          <input type="text" name="nome" class="form-control" placeholder="Ex: 2026" required>
        </div>
        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label">Data de Início</label>
            <input type="date" name="data_inicio" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Data de Termino</label>
            <input type="date" name="data_fim" class="form-control" required>
          </div>
        </div>
        <p class="text-muted text-small">Ao criar, o sistema gerará automaticamente os períodos (bimestres/trimestres) baseados na configuração acadêmica.</p>
      </div>
      <div class="modal__footer">
        <button type="button" class="btn btn--outline" onclick="closeModal('modal-ano')">Cancelar</button>
        <button type="submit" class="btn btn--primary">Criar Ano Letivo</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Disciplina -->
<div id="modal-disciplina" class="modal" style="display:none">
  <div class="modal__content">
    <div class="modal__header">
      <h3 id="disc-modal-title">Nova Disciplina</h3>
      <button class="modal__close" onclick="closeModal('modal-disciplina')">&times;</button>
    </div>
    <form id="disc-form" method="POST" action="/configuracoes/disciplinas">
      <?= csrfField() ?>
      <div class="modal__body">
        <div class="form-group">
          <label class="form-label">Nome da Disciplina</label>
          <input type="text" id="disc-nome" name="nome" class="form-control" required>
        </div>
        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label">Carga Horária Semanal</label>
            <input type="number" id="disc-carga" name="carga_horaria_semanal" class="form-control" value="2" required>
          </div>
          <div class="form-group">
            <label class="form-label">Cor no Calendário</label>
            <input type="color" id="disc-cor" name="cor_icone" class="form-control" value="#3b82f6" required>
          </div>
        </div>
      </div>
      <div class="modal__footer">
        <button type="button" class="btn btn--outline" onclick="closeModal('modal-disciplina')">Cancelar</button>
        <button type="submit" class="btn btn--primary" id="disc-btn-save">Salvar Disciplina</button>
      </div>
    </form>
  </div>
</div>

<script>
function showTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-' + tab).style.display = 'block';
  event.target.classList.add('active');
}

function openModal(id) {
  document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function editDisciplina(disc) {
  document.getElementById('disc-modal-title').innerText = 'Editar Disciplina';
  document.getElementById('disc-form').action = '/configuracoes/disciplinas/' + disc.id;
  document.getElementById('disc-nome').value = disc.nome;
  document.getElementById('disc-carga').value = disc.carga_horaria_semanal;
  document.getElementById('disc-cor').value = disc.cor_icone;
  document.getElementById('disc-btn-save').innerText = 'Atualizar Disciplina';
  openModal('modal-disciplina');
}

// Resetar modal de disciplina ao abrir pelo botão "Novo"
document.addEventListener('DOMContentLoaded', () => {
    const btnNovoDisc = document.getElementById('btn-nova-disciplina');
    if (btnNovoDisc) {
        btnNovoDisc.addEventListener('click', () => {
            document.getElementById('disc-modal-title').innerText = 'Nova Disciplina';
            document.getElementById('disc-form').action = '/configuracoes/disciplinas';
            document.getElementById('disc-form').reset();
            document.getElementById('disc-btn-save').innerText = 'Salvar Disciplina';
        });
    }
});
</script>
