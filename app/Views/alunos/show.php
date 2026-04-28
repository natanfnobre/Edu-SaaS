<div class="page-header">
  <div class="page-header__text">
    <h1><?= e($aluno['nome_completo']) ?></h1>
    <p>Informações completas do aluno</p>
  </div>
  <div class="page-header__actions">
    <a href="/notas/boletim/<?= $aluno['id'] ?>" class="btn btn--secondary">📜 Ver Boletim Acadêmico</a>
    <?php if (can('alunos.editar')): ?>
      <a href="/alunos/<?= $aluno['id'] ?>/editar" class="btn btn--primary">✏️ Editar</a>
    <?php endif; ?>
  </div>
</div>

<div class="grid grid-3" style="gap:1.5rem">

  <!-- Dados Pessoais -->
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Dados Pessoais</h3>
    </div>
    <div class="card__body">
      
      <?php if ($aluno['foto_path']): ?>
        <div style="text-align:center;margin-bottom:1rem">
          <img 
            src="/assets/uploads/<?= e($aluno['foto_path']) ?>" 
            alt="Foto" 
            style="width:120px;height:120px;border-radius:var(--radius-md);object-fit:cover;border:2px solid var(--border)"
          >
        </div>
      <?php endif; ?>

      <div style="display:grid;gap:.75rem;font-size:.9rem">
        <div>
          <strong style="color:var(--text-muted);font-size:.8rem">Data de Nascimento</strong><br>
          <?= $aluno['data_nascimento'] ? dateBr($aluno['data_nascimento']) : '--' ?>
        </div>
        <div>
          <strong style="color:var(--text-muted);font-size:.8rem">CPF</strong><br>
          <?= $aluno['cpf'] ? maskCpf($aluno['cpf']) : '--' ?>
        </div>
        <div>
          <strong style="color:var(--text-muted);font-size:.8rem">RG</strong><br>
          <?= e($aluno['rg'] ?? '--') ?>
        </div>
        <div>
          <strong style="color:var(--text-muted);font-size:.8rem">Naturalidade</strong><br>
          <?= e($aluno['naturalidade'] ?? '--') ?>
        </div>
        <div>
          <strong style="color:var(--text-muted);font-size:.8rem">Nacionalidade</strong><br>
          <?= e($aluno['nacionalidade'] ?? '--') ?>
        </div>
        <?php if ($aluno['necessidades_especiais']): ?>
          <div>
            <strong style="color:var(--text-muted);font-size:.8rem">⚠️ Necessidades Especiais</strong><br>
            <?= nl2br(e($aluno['necessidades_especiais'])) ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Endereço -->
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Endereço</h3>
    </div>
    <div class="card__body">
      <?php if ($aluno['endereco']): ?>
        <div style="font-size:.9rem;line-height:1.7">
          <?= e($aluno['endereco']['logradouro'] ?? '') ?>, 
          <?= e($aluno['endereco']['numero'] ?? 'S/N') ?>
          <?php if ($aluno['endereco']['complemento']): ?>
            - <?= e($aluno['endereco']['complemento']) ?>
          <?php endif; ?>
          <br>
          <?= e($aluno['endereco']['bairro'] ?? '') ?>
          <br>
          <?= e($aluno['endereco']['cidade'] ?? '') ?> - <?= e($aluno['endereco']['estado'] ?? '') ?>
          <br>
          CEP <?= e($aluno['endereco']['cep'] ?? '--') ?>
        </div>
      <?php else: ?>
        <p class="text-muted">Endereço não cadastrado</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Turmas -->
  <div class="card">
    <div class="card__header">
      <h3 class="card__title">Turmas</h3>
    </div>
    <div class="card__body">
      <?php if (empty($turmas)): ?>
        <p class="text-muted text-small">Aluno não está matriculado em nenhuma turma</p>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.5rem">
          <?php foreach ($turmas as $turma): ?>
            <a href="/turmas/<?= $turma['id'] ?>" class="btn btn--ghost btn--sm" style="justify-content:flex-start">
              🏫 <?= e($turma['nome']) ?> - <?= e($turma['ano_letivo']) ?>
              <span class="badge badge--neutral" style="margin-left:auto"><?= e(ucfirst($turma['status'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <?php if (can('alunos.editar')): ?>
        <hr style="margin:1rem 0">
        <form method="POST" action="/alunos/matricula" style="display:flex;flex-direction:column;gap:.75rem">
          <?= csrfField() ?>
          <input type="hidden" name="aluno_id" value="<?= (int)$aluno['id'] ?>">

          <div class="form-row form-row--2">
            <div class="form-group">
              <label class="form-label form-label--required">Ano Letivo</label>
              <select name="ano_letivo_id" class="form-control" required>
                <option value="">Selecione o ano</option>
                <?php foreach ($anos as $ano): ?>
                  <option value="<?= $ano['id'] ?>"><?= e($ano['nome']) ?> (<?= date('Y', strtotime($ano['data_inicio'])) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label form-label--required">Turma</label>
              <select name="turma_id" class="form-control" required>
                <option value="">Selecione a turma</option>
                <?php foreach ($turmas_all as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= e($t['nome']) ?> - <?= e($t['serie'] ?? '') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="text-align:right">
            <button class="btn btn--primary btn--sm" type="submit">📥 Matrícula Rápida</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Responsáveis -->
<div class="card" style="margin-top:1.5rem">
  <div class="card__header">
    <h3 class="card__title">Responsáveis / Pais</h3>
    <?php if (can('alunos.editar')): ?>
      <a href="/alunos/<?= $aluno['id'] ?>/responsaveis/novo" class="btn btn--sm btn--primary">➕ Adicionar</a>
    <?php endif; ?>
  </div>
  <div class="card__body" style="padding:0">
    <?php if (empty($aluno['responsaveis'])): ?>
      <div class="empty-state">
        <div class="empty-state__icon">👨‍👩‍👧</div>
        <div class="empty-state__text">Nenhum responsável cadastrado</div>
        <?php if (can('alunos.editar')): ?>
          <a href="/alunos/<?= $aluno['id'] ?>/responsaveis/novo" class="btn btn--primary" style="margin-top:.75rem">
            ➕ Adicionar Responsável
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Parentesco</th>
              <th>Telefone</th>
              <th>E-mail</th>
              <th>CPF</th>
              <th>Emergência</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($aluno['responsaveis'] as $resp): ?>
              <tr>
                <td>
                  <strong><?= e($resp['nome_completo']) ?></strong>
                  <?php if ($resp['contato_emergencia']): ?>
                    <span class="badge badge--danger badge--sm" style="font-size:.65rem;margin-left:.25rem">SOS</span>
                  <?php endif; ?>
                </td>
                <td><?= e(ucfirst($resp['parentesco'])) ?></td>
                <td><?= e($resp['telefone'] ?? '--') ?></td>
                <td><?= e($resp['email'] ?? '--') ?></td>
                <td><?= $resp['cpf'] ? maskCpf($resp['cpf']) : '--' ?></td>
                <td>
                  <?php if ($resp['contato_emergencia']): ?>
                    <span class="badge badge--danger">Sim</span>
                  <?php else: ?>
                    <span class="badge badge--neutral">Não</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
  
  <?php if (!empty($turmas) && can('alunos.editar')): ?>
    <div class="card" style="margin-top:1.5rem">
      <div class="card__header">
        <h3 class="card__title">Transferência / Cancelamento de Matrícula</h3>
      </div>
      <div class="card__body">
        <div style="margin-bottom:1rem">
          <strong>Matrículas Atuais:</strong>
          <ul>
            <?php foreach ($turmas as $t): ?>
              <li><?= e($t['nome']) ?> — <?= e($t['ano_letivo']) ?>
                <form method="POST" action="/alunos/matricula/cancelar" style="display:inline-block;margin-left:.5rem">
                  <?= csrfField() ?>
                  <input type="hidden" name="aluno_id" value="<?= (int)$aluno['id'] ?>">
                  <input type="hidden" name="ano_letivo_id" value="<?= (int)$t['ano_letivo_id'] ?>">
                  <button class="btn btn--sm btn--danger" type="submit" onclick="return confirm('Cancelar matrícula desta turma?');">Cancelar</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div>
          <form method="POST" action="/alunos/transferir" style="display:flex;gap:.5rem;align-items:center">
            <?= csrfField() ?>
            <input type="hidden" name="aluno_id" value="<?= (int)$aluno['id'] ?>">
            <select name="turma_destino_id" class="form-control" required style="flex:1">
              <option value="">Selecione a turma destino</option>
              <?php foreach ($turmas_all as $ta): ?>
                <option value="<?= (int)$ta['id'] ?>"><?= e($ta['nome']) ?> — <?= e($ta['serie'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn--primary" type="submit">Transferir</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Diário de Anotações -->
    <div class="card" style="margin-top:1.5rem">
      <div class="card__header" style="justify-content:space-between">
        <h3 class="card__title">📓 Diário do Aluno</h3>
      </div>
      <div class="card__body">
        
        <!-- Formulário Rápido (Se Professor ou Coordenador) -->
        <?php if (in_array(currentUser()['papel'], ['professor', 'coordenador', 'diretor', 'super_admin'])): ?>
          <form method="POST" action="/diario/aluno/<?= $aluno['id'] ?>" class="mb-4" style="background:var(--gray-50); padding:1rem; border-radius:8px; border:1px solid var(--border-color);">
            <?= csrfField() ?>
            <h4 style="margin-bottom:1rem; font-size:1rem;">Nova Anotação</h4>
            <div class="form-row form-row--2">
                <div class="form-group">
                   <label class="form-label form-label--required">Título/Resumo</label>
                   <input type="text" name="titulo" class="form-control" placeholder="Motivo da anotação" required>
                </div>
                <div class="form-group">
                   <label class="form-label form-label--required">Categoria</label>
                   <select name="categoria" class="form-control" required>
                       <option value="comportamento">🎭 Comportamento</option>
                       <option value="aprendizado">📚 Aprendizado</option>
                       <option value="elogio">⭐ Elogio / Destaque</option>
                       <option value="saude">⚕️ Saúde</option>
                       <option value="familiar">👨‍👩‍👦 Fato Familiar</option>
                       <option value="outro">📌 Outro</option>
                   </select>
                </div>
            </div>
            
            <div class="form-group">
               <label class="form-label form-label--required">Registro Detalhado</label>
               <textarea name="conteudo" class="form-control" rows="3" placeholder="O que ocorreu hoje?" required></textarea>
            </div>

            <div class="form-row form-row--2" style="align-items:flex-end">
               <div class="form-group">
                 <label class="form-label form-label--required">Sigilo (Quem pode ver?)</label>
                 <select name="visibilidade" class="form-control" required>
                     <?php if (in_array(currentUser()['papel'], ['coordenador', 'diretor'])): ?>
                       <option value="coordenacao">Apenas Coordenação</option>
                     <?php endif; ?>
                     <option value="somente_autor">Somente Eu (Autor)</option>
                     <option value="professores">Equipe Escolar (Professores/Coordenadores)</option>
                     <option value="todos">Todos (Incluindo os Pais)</option>
                 </select>
               </div>
               <div class="form-group" style="text-align:right">
                 <button type="submit" class="btn btn--primary">Adicionar ao Diário</button>
               </div>
            </div>
          </form>
        <?php endif; ?>

        <!-- Timeline Flow -->
        <div class="timeline" style="margin-top:2rem;">
           <?php if (empty($anotacoes)): ?>
              <div class="empty-state">
                 <div class="empty-state__icon">📄</div>
                 <div class="empty-state__title">Diário Limpo</div>
                 <div class="empty-state__text">Nenhuma anotação disponível registrada no arquivo do aluno (ou sem permissão de leitura).</div>
              </div>
           <?php else: ?>
              
              <div style="display:flex; flex-direction:column; gap:1.5rem; position:relative; border-left:2px solid var(--gray-200); padding-left:1.5rem; margin-left:.5rem;">
                <?php foreach ($anotacoes as $note): 
                    // Color Mapping base das categorias
                    $colors = [
                        'comportamento' => 'var(--warning)', 
                        'aprendizado' => 'var(--primary)',
                        'elogio' => 'var(--success)',
                        'saude' => 'var(--danger)',
                        'familiar' => '#8b5cf6',
                        'outro' => 'var(--gray-500)'
                    ];
                    // Name mapping base Visibilidade 
                    $tags = [
                        'somente_autor' => 'Somente Autor',
                        'professores' => 'Equipe Escolar',
                        'coordenacao' => 'Apenas Coordenação',
                        'todos' => 'Público/Pais'
                    ];
                ?>
                  
                  <div class="timeline-item" style="position:relative;">
                     <!-- Bullet point da linha do tempo -->
                     <span style="position:absolute; left:-2rem; top:0; width:1rem; height:1rem; background:<?= $colors[$note['categoria']] ?>; border-radius:50%; outline: 4px solid #fff;"></span>
                     
                     <div style="background:#fff; border:1px solid var(--border-color); border-radius:8px; padding:1rem; box-shadow:0 1px 3px rgba(0,0,0,.05);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.5rem;">
                           <div style="display:flex; flex-direction:column; gap:.25rem;">
                               <strong style="font-size:1.1rem;"><?= e($note['titulo']) ?></strong>
                               <div style="display:flex; align-items:center; gap:.5rem; font-size:0.85rem; color:var(--text-muted)">
                                   <span style="color:<?= $colors[$note['categoria']] ?>; font-weight:600; text-transform:uppercase;"><?= e($note['categoria']) ?></span>
                                   &bull; Visibilidade: <?= e($tags[$note['visibilidade']] ?? 'Geral') ?>
                               </div>
                           </div>
                           <time style="font-size:0.85rem; color:var(--text-muted)"><?= date('d/m/Y H:i', strtotime($note['criado_em'])) ?></time>
                        </div>
                        
                        <p style="margin:1rem 0; line-height:1.5; padding: .5rem 0; color:var(--text-color)"><?= nl2br(e($note['conteudo'])) ?></p>

                        <!-- Rodapé da anotação (Autor) -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:.5rem; padding-top:.75rem; border-top:1px dashed var(--gray-200);">
                            <div style="display:flex; align-items:center; gap:.5rem; font-size:.85rem; color:var(--text-muted)">
                                <div class="avatar avatar--sm avatar--initials" style="width:24px;height:24px;font-size:.65rem;"><?= mb_substr($note['autor_nome'], 0, 1) ?></div>
                                Registrado por <?= e($note['autor_nome']) ?> (<?= ucfirst(e($note['autor_papel'])) ?>)
                            </div>
                            
                            <!-- Botões de ação (Só se ele foi o autor ou Admin) -->
                            <?php if ($note['autor_id'] == currentUser()['id'] || in_array(currentUser()['papel'], ['diretor', 'super_admin'])): ?>
                                <form method="POST" action="/diario/<?= $note['id'] ?>/deletar" onsubmit="return confirm('Deseja apagar esta entrada?');">
                                    <?= csrfField() ?>
                                    <button class="btn btn--sm btn--ghost" style="color:var(--danger)">🗑️ Apagar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                     </div>
                  </div>
                  
                <?php endforeach; ?>
              </div>

           <?php endif; ?>
        </div>

      </div>
    </div>
