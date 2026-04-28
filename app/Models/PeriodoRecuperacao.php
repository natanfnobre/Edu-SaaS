<?php

namespace App\Models;

class PeriodoRecuperacao extends BaseModel
{
    protected string $table = 'periodos_recuperacao';

    /**
     * Retorna todos os períodos de recuperação do ano letivo de um tenant,
     * incluindo quem criou e a qual bimestre se refere (se houver)
     */
    public function listarPorAnoLetivo(int $tenantId, int $anoLetivoId): array
    {
        $sql = "
            SELECT pr.*, 
                   p.nome as periodo_referencia_nome,
                   u.nome as autor_nome,
                   (SELECT COUNT(*) FROM notas_recuperacao nr WHERE nr.periodo_recuperacao_id = pr.id) as total_notas_lancadas
            FROM {$this->table} pr
            LEFT JOIN periodos p ON pr.periodo_id = p.id
            JOIN users u ON pr.aberto_por = u.id
            WHERE pr.tenant_id = :tenant_id 
              AND pr.ano_letivo_id = :ano_letivo_id
            ORDER BY pr.data_inicio DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'ano_letivo_id' => $anoLetivoId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca os períodos de recuperação "ativos" ou "no prazo" na data atual.
     */
    public function buscarVigentes(int $tenantId, int $anoLetivoId): array
    {
        $hoje = date('Y-m-d');
        $sql = "
            SELECT pr.*, p.nome as periodo_referencia_nome
            FROM {$this->table} pr
            LEFT JOIN periodos p ON pr.periodo_id = p.id
            WHERE pr.tenant_id = :tenant_id 
              AND pr.ano_letivo_id = :ano_letivo_id
              AND pr.ativo = 1
              AND :hoje BETWEEN pr.data_inicio AND pr.data_fim
            ORDER BY pr.data_inicio ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'ano_letivo_id' => $anoLetivoId, 'hoje' => $hoje]);
        return $stmt->fetchAll();
    }
}
