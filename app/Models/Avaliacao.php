<?php

namespace App\Models;

class Avaliacao extends BaseModel
{
    protected string $table = 'avaliacoes';

    /**
     * Retorna as avaliações de uma turma+disciplina+período.
     */
    public function porTurmaEPeriodo(int $turmaId, int $periodoId, int $disciplinaId, int $tenantId): array
    {
        return $this->query(
            'SELECT * FROM avaliacoes
             WHERE turma_id = ? AND periodo_id = ? AND disciplina_id = ? AND tenant_id = ?
             ORDER BY ordem ASC',
            [$turmaId, $periodoId, $disciplinaId, $tenantId]
        )->fetchAll();
    }

    /**
     * Cria avaliações padrão (AV1, AV2...) para uma turma+disciplina+período.
     * Usado quando a turma não tem avaliações configuradas ainda.
     */
    public function criarPadrao(int $turmaId, int $disciplinaId, int $periodoId, int $tenantId, int $quantidade = 2): void
    {
        $existentes = $this->porTurmaEPeriodo($turmaId, $periodoId, $disciplinaId, $tenantId);
        if (!empty($existentes)) {
            return; // Já existem — não duplicar
        }

        for ($i = 1; $i <= $quantidade; $i++) {
            $this->create([
                'tenant_id'    => $tenantId,
                'disciplina_id'=> $disciplinaId,
                'turma_id'     => $turmaId,
                'periodo_id'   => $periodoId,
                'nome'         => "AV{$i}",
                'peso'         => 1.00,
                'nota_maxima'  => 10.00,
                'ordem'        => $i,
            ]);
        }
    }

    /**
     * Retorna todas as avaliações de uma turma (todos os períodos) com nome do período.
     */
    public function porTurmaCompleto(int $turmaId, int $tenantId): array
    {
        return $this->query(
            'SELECT av.*, p.nome as periodo_nome, p.numero as periodo_numero,
                    d.nome as disciplina_nome
             FROM avaliacoes av
             INNER JOIN periodos p    ON p.id = av.periodo_id
             INNER JOIN disciplinas d ON d.id = av.disciplina_id
             WHERE av.turma_id = ? AND av.tenant_id = ?
             ORDER BY p.numero ASC, d.nome ASC, av.ordem ASC',
            [$turmaId, $tenantId]
        )->fetchAll();
    }
}
