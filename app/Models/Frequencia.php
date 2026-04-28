<?php

namespace App\Models;

class Frequencia extends BaseModel
{
    protected string $table = 'frequencias';

    /**
     * Salva a frequência de todos os alunos de uma aula em lote.
     *
     * @param int   $aulaId
     * @param array $frequencias  Array de aluno_id => bool (true = presente, false = falta)
     */
    public function salvarLote(int $aulaId, array $frequencias, int $tenantId): void
    {
        if (empty($frequencias)) return;

        $this->transaction(function () use ($aulaId, $frequencias, $tenantId) {
            foreach ($frequencias as $alunoId => $presente) {
                $this->query(
                    'INSERT INTO frequencias (tenant_id, aula_id, aluno_id, presente)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE presente = VALUES(presente)',
                    [$tenantId, $aulaId, (int) $alunoId, $presente ? 1 : 0]
                );
            }
        });
    }

    /**
     * Resumo de frequência de um aluno em uma disciplina/turma.
     * Retorna: total_aulas, total_presentes, total_faltas, percentual_faltas
     */
    public function resumoPorAluno(int $alunoId, int $turmaId, int $disciplinaId, int $tenantId): array
    {
        $result = $this->query(
            'SELECT
                COALESCE(SUM(a.numero_aulas), 0)                               as total_aulas,
                COALESCE(SUM(CASE WHEN f.presente = 1 THEN a.numero_aulas END), 0) as total_presentes,
                COALESCE(SUM(CASE WHEN f.presente = 0 THEN a.numero_aulas END), 0) as total_faltas
             FROM aulas a
             LEFT JOIN frequencias f ON f.aula_id = a.id AND f.aluno_id = ?
             WHERE a.turma_id = ? AND a.disciplina_id = ? AND a.tenant_id = ?',
            [$alunoId, $turmaId, $disciplinaId, $tenantId]
        )->fetch();

        $totalAulas  = (int) ($result['total_aulas'] ?? 0);
        $totalFaltas = (int) ($result['total_faltas'] ?? 0);
        $percentual  = $totalAulas > 0 ? round(($totalFaltas / $totalAulas) * 100, 1) : 0.0;

        return [
            'total_aulas'       => $totalAulas,
            'total_presentes'   => (int) ($result['total_presentes'] ?? 0),
            'total_faltas'      => $totalFaltas,
            'percentual_faltas' => $percentual,
        ];
    }

    /**
     * Retorna frequências de uma aula específica, indexadas por aluno_id.
     */
    public function porAula(int $aulaId, int $tenantId): array
    {
        $rows = $this->query(
            'SELECT aluno_id, presente, justificada, observacao
             FROM frequencias
             WHERE aula_id = ? AND tenant_id = ?',
            [$aulaId, $tenantId]
        )->fetchAll();

        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['aluno_id']] = $r;
        }
        return $indexed;
    }
}
