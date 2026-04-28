<?php

namespace App\Models;

class Comunicado extends BaseModel
{
    protected string $table = 'comunicados';

    /** Retorna comunicados aplicáveis a um responsável (todos/pais/turma) */
    public function forResponsavel(int $responsavelId, int $tenantId): array
    {
        // Busca aluno vinculado ao responsavel
        $stmt = $this->query('SELECT aluno_id FROM responsaveis WHERE id = ? AND tenant_id = ? LIMIT 1', [$responsavelId, $tenantId]);
        $r = $stmt->fetch();
        $alunoId = $r ? (int) $r['aluno_id'] : 0;

        // Busca turma atual do aluno (matricula ativa)
        $turmaId = null;
        if ($alunoId) {
            $stmt2 = $this->query('SELECT turma_id FROM matriculas WHERE aluno_id = ? AND tenant_id = ? AND status = "ativo" LIMIT 1', [$alunoId, $tenantId]);
            $m = $stmt2->fetch();
            $turmaId = $m ? (int) $m['turma_id'] : null;
        }

        // Busca comunicados do tenant filtrando por publico_alvo
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM comunicados_leitura cl WHERE cl.comunicado_id = c.id AND cl.responsavel_id = ?) as lido_por_mim
                FROM comunicados c
                WHERE c.tenant_id = ? AND (
                  c.publico_alvo = "todos" OR
                  c.publico_alvo = "pais" OR
                  (c.publico_alvo = "turma" AND c.turma_id = ?)
                )
                ORDER BY c.fixado DESC, c.criado_em DESC';

        $stmt3 = $this->query($sql, [$responsavelId, $tenantId, $turmaId]);
        return $stmt3->fetchAll();
    }
}
