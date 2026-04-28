<div class="page-header">
  <div class="page-header__text">
    <h1>Recuperação Acadêmica</h1>
    <p>Gerencie e visualize as Recuperações do Ano Letivo: <strong><?= e($anoAtivo['nome']) ?></strong></p>
  </div>
  <?php if ($isCoordenacao): ?>
  <div class="page-header__actions">
    <button type="button" class="btn btn--primary btn--sm" onclick="abrirModalNovaRec()">➕ Nova Recuperação</button>
  </div>
  <?php endif; ?>
</div>

<?php if (empty($periodos)): ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-state__icon">🔄</div>
      <div class="empty-state__title">Nenhum período de recuperação aberto</div>
      <div class="empty-state__text">A coordenação ainda não iniciou ou agendou as datas de recuperação neste ano letivo.</div>
    </div>
  </div>
<?php else: ?>
  <div class="grid grid-3">
    <?php foreach ($periodos as $pr): ?>
      <?php 
        $hoje = date('Y-m-d');
        $status = 'fechado';
        if (!$pr['ativo']) $status = 'cancelado';
        else if ($hoje < $pr['data_inicio']) $status = 'agendado';
        else if ($hoje <= $pr['data_fim']) $status = 'ativo';
      ?>
      <div class="card card--hoverable">
        <div class="card__header">
          <h3 class="card__title" style="font-size:.95rem"><?= e($pr['nome']) ?></h3>
          <?php if ($status === 'ativo'): ?>
            <span class="badge badge--success">● Ativo</span>
          <?php elseif ($status === 'agendado'): ?>
            <span class="badge badge--warning">Agendado</span>
          <?php else: ?>
            <span class="badge badge--neutral">Fechado</span>
          <?php endif; ?>
        </div>
        <div class="card__body">
          <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem">
            Ref: <?= $pr['periodo_referencia_nome'] ? e($pr['periodo_referencia_nome']) : 'Recuperação Global' ?>
          </p>
          <div style="font-size:.85rem; margin-bottom:.5rem;">
            <strong>Prazo:</strong> <?= date('d/m/Y', strtotime($pr['data_inicio'])) ?> até <?= date('d/m/Y', strtotime($pr['data_fim'])) ?>
          </div>
          <div class="stat-row" style="margin-bottom:.75rem">
            <span class="text-small text-muted">Aferições Registradas</span>
            <strong><?= $pr['total_notas_lancadas'] ?></strong>
          </div>
          
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a href="/notas" class="btn btn--sm btn--primary <?= $status !== 'ativo' ? 'btn--disabled' : '' ?>">
                Consultar Diários ->
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- MODAL COORDENAÇÃO: NOVO PERÍODO DE REC. -->
<?php if ($isCoordenacao): ?>
<div class="modal-backdrop" id="modalNovaRec" style="display:none">
  <div class="modal">
    <div class="modal__header">
      <h2 class="modal__title">Abrir Período de Recuperação</h2>
      <button class="modal__close" onclick="fecharModalNovaRec()">×</button>
    </div>
    <form action="/recuperacao/abrir" method="POST">
      <div class="modal__body">
          <div class="form-group">
            <label class="form-label form-label--required">Nome da Recuperação</label>
            <input type="text" name="nome" class="form-control" required placeholder="Ex: Recuperação do Primeiro Semestre">
          </div>
          
          <div class="form-group">
            <label class="form-label">Referência</label>
            <select name="periodo_id" class="form-control">
              <option value="">Nenhuma (Recuperação Final)</option>
              <!-- A inserção dos períodos poderia vir do Controller, mas simplificaremos via text label. -->
              <option value="1">1º Bimestre</option>
              <option value="2">2º Bimestre</option>
              <option value="3">3º Bimestre</option>
              <option value="4">4º Bimestre</option>
            </select>
            <span class="form-hint">Se esta for uma recuperação de um bimestre específico.</span>
          </div>

          <div class="form-row form-row--2">
            <div class="form-group">
              <label class="form-label form-label--required">Início dos Lançamentos</label>
              <input type="date" name="data_inicio" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label form-label--required">Fim (Bloqueio)</label>
              <input type="date" name="data_fim" class="form-control" required>
            </div>
          </div>
      </div>
      <div class="modal__footer">
        <button type="button" class="btn btn--ghost" onclick="fecharModalNovaRec()">Cancelar</button>
        <button type="submit" class="btn btn--primary">Salvar Calendário</button>
      </div>
    </form>
  </div>
</div>

<script>
  function abrirModalNovaRec() { document.getElementById('modalNovaRec').style.display = 'flex'; }
  function fecharModalNovaRec() { document.getElementById('modalNovaRec').style.display = 'none'; }
</script>
<?php endif; ?>
