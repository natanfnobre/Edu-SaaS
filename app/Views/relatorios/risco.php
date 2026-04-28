<div class="page-header">
  <div class="page-header__text">
    <h1>Alunos em Risco Acadêmico</h1>
    <p>Relação de todos os discentes com notas inferiores à média miníma requerida para o Período Letivo em vigência.</p>
  </div>
  <div class="page-header__actions">
     <button class="btn btn--outline" onclick="window.print()">🖨️ Imprimir Detalhamento</button>
  </div>
</div>

<div class="card">
  <div class="card__body" style="padding:0">
     <?php if (empty($alunos)): ?>
       <div class="empty-state">
           <div class="empty-state__icon">🎉</div>
           <div class="empty-state__title">Nenhum aluno estagnado</div>
           <div class="empty-state__text">Aparentemente sua escola está com índices de retenção e defasagem acadêmica perfeitamente saúdaveis!</div>
       </div>
     <?php else: ?>
     <div class="table-wrapper">
       <table class="table">
         <thead>
           <tr>
             <th style="width:250px">Aluno</th>
             <th>Razão (Turma/Disciplina)</th>
             <th>Média Crítica</th>
             <th style="width:100px;">Ações</th>
           </tr>
         </thead>
         <tbody>
           <?php foreach($alunos as $a): ?>
             <tr>
               <td>
                  <strong><?= e($a['aluno_nome']) ?></strong>
               </td>
               <td class="text-muted">
                  <?= e($a['turma_nome']) ?> - Módulo: <?= e($a['disciplina_nome']) ?>
               </td>
               <td>
                  <span class="badge badge--danger" style="font-size:1rem;">
                     <?= number_format($a['nota'], 1, ',', '.') ?>
                  </span>
               </td>
               <td>
                  <a href="/alunos/<?= $a['aluno_id'] ?>" class="btn btn--sm btn--outline">Abrir Ficha</a>
               </td>
             </tr>
           <?php endforeach; ?>
         </tbody>
       </table>
     </div>
     <?php endif; ?>
  </div>
</div>
