<?php

namespace App\Models;

class Disciplina extends BaseModel
{
    protected string $table = 'disciplinas';

    public function vincularProfessor(int $disciplinaId, int $professorId, int $turmaId, int $anoLetivoId, int $tenantId): bool
    {
        // Verifica se já existe
        $stmt = $this->query(
            'SELECT id FROM professor_disciplina_turma 
             WHERE user_id = ? AND disciplina_id = ? AND turma_id = ? AND ano_letivo_id = ? LIMIT 1',
            [$professorId, $disciplinaId, $turmaId, $anoLetivoId]
        );
        if ($stmt->fetch()) return false;

        $this->query(
            'INSERT INTO professor_disciplina_turma (tenant_id, user_id, disciplina_id, turma_id, ano_letivo_id) 
             VALUES (?, ?, ?, ?, ?)',
            [$tenantId, $professorId, $disciplinaId, $turmaId, $anoLetivoId]
        );

        return true;
    }

    public function desvincularProfessor(int $disciplinaId, int $professorId, int $turmaId): bool
    {
        return $this->query(
            'DELETE FROM professor_disciplina_turma 
             WHERE user_id = ? AND disciplina_id = ? AND turma_id = ?',
            [$professorId, $disciplinaId, $turmaId]
        )->rowCount() > 0;
    }

    public function turmasDoProfessor(int $professorId, int $disciplinaId, int $tenantId): array
    {
        $stmt = $this->query(
            'SELECT DISTINCT t.* 
             FROM turmas t
             INNER JOIN professor_disciplina_turma pdt ON pdt.turma_id = t.id
             WHERE pdt.user_id = ? AND pdt.disciplina_id = ? AND pdt.tenant_id = ?
             ORDER BY t.nome ASC',
            [$professorId, $disciplinaId, $tenantId]
        );
        return $stmt->fetchAll();
    }

    public function professoresDaDisciplina(int $disciplinaId, int $tenantId): array
    {
        $stmt = $this->query(
            'SELECT DISTINCT u.id, u.nome, u.email, t.nome as turma_nome
             FROM users u
             INNER JOIN professor_disciplina_turma pdt ON pdt.user_id = u.id
             INNER JOIN turmas t ON t.id = pdt.turma_id
             WHERE pdt.disciplina_id = ? AND pdt.tenant_id = ?
             ORDER BY u.nome ASC',
            [$disciplinaId, $tenantId]
        );
        return $stmt->fetchAll();
    }
}
