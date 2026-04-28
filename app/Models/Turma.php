<?php

namespace App\Models;

class Turma extends BaseModel
{
    protected string $table = 'turmas';

    public function porAnoLetivo(int $anoLetivoId, int $tenantId): array
    {
        return $this->findBy(['ano_letivo_id' => $anoLetivoId, 'ativo' => 1], $tenantId, 'nome ASC');
    }

    public function withDetalhes(int $turmaId, int $tenantId): ?array
    {
        $turma = $this->findById($turmaId, $tenantId);
        if (!$turma) return null;

        // Ano letivo
        $stmt = $this->query('SELECT * FROM anos_letivos WHERE id = ? LIMIT 1', [$turma['ano_letivo_id']]);
        $turma['ano_letivo'] = $stmt->fetch();

        // Total de alunos
        $stmt = $this->query(
            'SELECT COUNT(*) FROM matriculas WHERE turma_id = ? AND status = "ativo"',
            [$turmaId]
        );
        $turma['total_alunos'] = (int) $stmt->fetchColumn();

        // Disciplinas vinculadas
        $stmt = $this->query(
            'SELECT DISTINCT d.id, d.nome, d.cor_icone
             FROM disciplinas d
             INNER JOIN professor_disciplina_turma pdt ON pdt.disciplina_id = d.id
             WHERE pdt.turma_id = ? AND pdt.tenant_id = ?
             ORDER BY d.nome ASC',
            [$turmaId, $tenantId]
        );
        $turma['disciplinas'] = $stmt->fetchAll();

        // Professores vinculados
        $stmt = $this->query(
            'SELECT DISTINCT u.id, u.nome, u.email, d.nome as disciplina_nome
             FROM users u
             INNER JOIN professor_disciplina_turma pdt ON pdt.user_id = u.id
             INNER JOIN disciplinas d ON d.id = pdt.disciplina_id
             WHERE pdt.turma_id = ? AND pdt.tenant_id = ?
             ORDER BY u.nome ASC',
            [$turmaId, $tenantId]
        );
        $turma['professores'] = $stmt->fetchAll();

        return $turma;
    }

    public function alunos(int $turmaId, int $tenantId): array
    {
        $stmt = $this->query(
            'SELECT a.*, m.numero_matricula, m.data_matricula, m.status
             FROM alunos a
             INNER JOIN matriculas m ON m.aluno_id = a.id
             WHERE m.turma_id = ? AND a.tenant_id = ? AND a.ativo = 1
             ORDER BY a.nome_completo ASC',
            [$turmaId, $tenantId]
        );
        return $stmt->fetchAll();
    }

    public function adicionarAluno(int $turmaId, int $alunoId, int $anoLetivoId, int $tenantId, string $numeroMatricula = null): bool
    {
        // Verifica se o aluno já possui matrícula ATIVA no mesmo ano letivo (em qualquer turma)
        $stmt = $this->query(
            'SELECT id FROM matriculas WHERE aluno_id = ? AND ano_letivo_id = ? AND status = "ativo" LIMIT 1',
            [$alunoId, $anoLetivoId]
        );
        if ($stmt->fetch()) return false; // Já matriculado naquele ano letivo

        $this->query(
            'INSERT INTO matriculas (tenant_id, aluno_id, turma_id, ano_letivo_id, numero_matricula, data_matricula, status) 
             VALUES (?, ?, ?, ?, ?, ?, "ativo")',
            [$tenantId, $alunoId, $turmaId, $anoLetivoId, $numeroMatricula, date('Y-m-d')]
        );

        return true;
    }

    public function removerAluno(int $turmaId, int $alunoId): bool
    {
        return $this->query(
            'UPDATE matriculas SET status = "cancelado" WHERE turma_id = ? AND aluno_id = ?',
            [$turmaId, $alunoId]
        )->rowCount() > 0;
    }
}
