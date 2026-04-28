<?php

namespace App\Models;

use PDO;

class NotaRecuperacao extends BaseModel
{
    protected string $table = 'notas_recuperacao';

    /**
     * Retorna todas as notas de recuperação já lançadas de uma turma + disciplina.
     * Útil quando o professor volta para editar a recuperação.
     */
    public function obterLancamentos(int $tenantId, int $periodoRecId, int $turmaId, int $disciplinaId): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE tenant_id = :tenant_id
              AND periodo_recuperacao_id = :periodo_rec_id
              AND turma_id = :turma_id
              AND disciplina_id = :disciplina_id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tenant_id' => $tenantId,
            'periodo_rec_id' => $periodoRecId,
            'turma_id' => $turmaId,
            'disciplina_id' => $disciplinaId
        ]);
        
        // Retorna mapeado pela chave aluno_id para facilitar o front-end
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['aluno_id']] = $row;
        }
        return $result;
    }

    /**
     * Salva as notas de recuperação de toda a turma para aquela disciplina num único batch.
     */
    public function salvarLote(
        int $tenantId, 
        int $periodoRecId, 
        int $turmaId, 
        int $disciplinaId, 
        array $notasEnviadas, 
        int $userId
    ): void {
        $sql = "
            INSERT INTO {$this->table} 
            (tenant_id, periodo_recuperacao_id, aluno_id, disciplina_id, turma_id, nota, lancado_por, nota_substituiu, nota_anterior)
            VALUES 
            (:tenant_id, :periodo_rec_id, :aluno_id, :disciplina_id, :turma_id, :nota, :lancado_por, :nota_substituiu, :nota_anterior)
            ON DUPLICATE KEY UPDATE 
            nota = VALUES(nota), 
            lancado_por = VALUES(lancado_por),
            lancado_em = NOW(),
            nota_substituiu = VALUES(nota_substituiu),
            nota_anterior = VALUES(nota_anterior)
        ";
        
        $stmt = $this->db->prepare($sql);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($notasEnviadas as $alunoId => $dadosVindosDoService) {
                // Se veio vazio, o cara apagou a nota. Podemos definir para NULL mas geralmente se deixa em branco.
                if ($dadosVindosDoService['nota'] === null || $dadosVindosDoService['nota'] === '') {
                    continue; // Ignora salvar vazios ou podemos deletar via logic - por brevidade, apenas ignorar vazio
                }
                
                $stmt->execute([
                    'tenant_id' => $tenantId,
                    'periodo_rec_id' => $periodoRecId,
                    'aluno_id' => $alunoId,
                    'disciplina_id' => $disciplinaId,
                    'turma_id' => $turmaId,
                    'nota' => $dadosVindosDoService['nota'],
                    'lancado_por' => $userId,
                    'nota_substituiu' => $dadosVindosDoService['nota_substituiu'] ? 1 : 0,
                    'nota_anterior' => $dadosVindosDoService['nota_anterior']
                ]);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw clone $e; 
        }
    }
}
