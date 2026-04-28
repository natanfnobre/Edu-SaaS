<?php

namespace App\Models;

class Aula extends BaseModel
{
    protected string $table = 'aulas';

    /**
     * Retorna as aulas de uma turma+disciplina com total de alunos presentes.
     */
    public function porTurmaEDisciplina(int $turmaId, int $disciplinaId, int $tenantId): array
    {
        return $this->query(
            'SELECT a.*,
                    p.nome as periodo_nome,
                    COUNT(f.id)                               as total_frequencias,
                    SUM(CASE WHEN f.presente = 1 THEN 1 END) as total_presentes,
                    SUM(CASE WHEN f.presente = 0 THEN 1 END) as total_faltas
             FROM aulas a
             INNER JOIN periodos p ON p.id = a.periodo_id
             LEFT JOIN frequencias f ON f.aula_id = a.id
             WHERE a.turma_id = ? AND a.disciplina_id = ? AND a.tenant_id = ?
             GROUP BY a.id
             ORDER BY a.data DESC',
            [$turmaId, $disciplinaId, $tenantId]
        )->fetchAll();
    }

    /**
     * Conta o total de aulas (número de aulas, não registros) de um aluno em uma disciplina/turma.
     */
    public function totalAulasPorTurmaEDisciplina(int $turmaId, int $disciplinaId, int $tenantId): int
    {
        $result = $this->query(
            'SELECT COALESCE(SUM(numero_aulas), 0) as total
             FROM aulas
             WHERE turma_id = ? AND disciplina_id = ? AND tenant_id = ?',
            [$turmaId, $disciplinaId, $tenantId]
        )->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Verifica se já há aula registrada nesta data para a turma+disciplina.
     */
    public function existeNaData(int $turmaId, int $disciplinaId, string $data, int $tenantId): bool
    {
        $result = $this->query(
            'SELECT id FROM aulas WHERE turma_id = ? AND disciplina_id = ? AND data = ? AND tenant_id = ? LIMIT 1',
            [$turmaId, $disciplinaId, $data, $tenantId]
        )->fetch();
        return !empty($result);
    }

    /**
     * Período ativo da turma (para descobrir em qual período a aula se encaixa pela data).
     */
    public function periodoAtivoPorData(int $turmaId, string $data, int $tenantId): ?array
    {
        $turmaQuery = $this->query(
            'SELECT ano_letivo_id FROM turmas WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$turmaId, $tenantId]
        )->fetch();

        if (!$turmaQuery) return null;

        return $this->query(
            'SELECT * FROM periodos
             WHERE ano_letivo_id = ? AND tenant_id = ?
               AND data_inicio <= ? AND data_fim >= ?
             LIMIT 1',
            [$turmaQuery['ano_letivo_id'], $tenantId, $data, $data]
        )->fetch() ?: null;
    }
}
