<?php

namespace App\Controllers;

use App\Helpers\View;
use App\Models\AnoLetivo;

class DashboardController
{
    public function index(): void
    {
        $user   = currentUser();
        $role   = auth()->role();
        $tenant = $_SESSION['tenant_id'];

        // Dashboard varia por papel
        match ($role) {
            'super_admin'             => $this->dashboardSuperAdmin(),
            'diretor', 'coordenador'  => $this->dashboardCoordenacao($tenant),
            'secretaria'              => $this->dashboardSecretaria($tenant),
            'professor'               => $this->dashboardProfessor($tenant, (int) $user['id']),
            default                   => $this->dashboardDefault(),
        };
    }

    private function dashboardSuperAdmin(): void
    {
        // TODO: Visão de todas as escolas
        View::render('dashboard/super-admin', [
            'pageTitle' => 'Painel Geral',
        ]);
    }

    private function dashboardCoordenacao(int $tenantId): void
    {
        // TODO: Estatísticas gerais da escola
        $stats = [
            'total_alunos'   => $this->getTotalAlunos($tenantId),
            'total_turmas'   => $this->getTotalTurmas($tenantId),
            'total_professores' => $this->getTotalProfessores($tenantId),
            'alunos_risco'   => $this->getAlunosRisco($tenantId),
        ];

        View::render('dashboard/coordenacao', [
            'pageTitle' => 'Dashboard',
            'stats'     => $stats,
        ]);
    }

    private function dashboardSecretaria(int $tenantId): void
    {
        View::render('dashboard/secretaria', [
            'pageTitle' => 'Dashboard',
        ]);
    }

    private function dashboardProfessor(int $tenantId, int $userId): void
    {
        $anoAtivo = (new AnoLetivo())->findOneBy(['ativo' => 1], $tenantId);

        $stats = [
            'minhas_turmas'    => $this->getTurmasProfessor($userId, $tenantId),
            'pendencias_notas' => $anoAtivo ? $this->getPendenciasNotasProfessor($userId, $tenantId, $anoAtivo['id']) : 0,
            'alunos_atencao'   => $anoAtivo ? $this->getAlunosAtencaoProfessor($userId, $tenantId) : 0,
        ];

        View::render('dashboard/professor', [
            'pageTitle' => 'Minhas Turmas',
            'stats'     => $stats,
            'anoAtivo'  => $anoAtivo,
        ]);
    }

    private function dashboardDefault(): void
    {
        View::render('dashboard/index', [
            'pageTitle' => 'Dashboard',
        ]);
    }

    public function riscos(): void
    {
        $tenantId = tenantId();
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare(
            'SELECT a.id as aluno_id, a.nome_completo as aluno_nome, t.nome as turma_nome, d.nome as disciplina_nome, n.nota
             FROM notas n
             INNER JOIN avaliacoes av ON av.id = n.avaliacao_id
             INNER JOIN periodos p ON p.id = av.periodo_id
             INNER JOIN anos_letivos al ON al.id = p.ano_letivo_id
             INNER JOIN alunos a ON a.id = n.aluno_id
             INNER JOIN turmas t ON t.id = av.turma_id
             INNER JOIN disciplinas d ON d.id = av.disciplina_id
             WHERE n.tenant_id = ? AND al.ativo = 1 AND n.nota < 6
             ORDER BY n.nota ASC'
        );
        $stmt->execute([$tenantId]);
        $alunos = $stmt->fetchAll();

        \App\Helpers\View::render('relatorios/risco', [
            'pageTitle' => 'Alunos em Risco',
            'alunos'    => $alunos,
        ]);
    }

    // ── Helpers de dados ─────────────────────────────────────────

    private function getTotalAlunos(int $tenantId): int
    {
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM alunos WHERE tenant_id = ? AND ativo = 1');
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }

    private function getTotalTurmas(int $tenantId): int
    {
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM turmas WHERE tenant_id = ? AND ativo = 1');
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }

    private function getTotalProfessores(int $tenantId): int
    {
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE tenant_id = ? AND papel = "professor" AND ativo = 1');
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }

    private function getAlunosRisco(int $tenantId): int
    {
        $db = \App\Helpers\Database::getInstance();
        // Alunos com média abaixo de 6 em pelo menos uma disciplina
        $stmt = $db->prepare(
            'SELECT COUNT(DISTINCT n.aluno_id) as total
             FROM notas n
             INNER JOIN avaliacoes av ON av.id = n.avaliacao_id
             INNER JOIN periodos p ON p.id = av.periodo_id
             INNER JOIN anos_letivos al ON al.id = p.ano_letivo_id
             WHERE n.tenant_id = ? AND al.ativo = 1 AND n.nota < 6'
        );
        $stmt->execute([$tenantId]);
        return (int) ($stmt->fetchColumn() ?? 0);
    }

    private function getPendenciasNotasProfessor(int $userId, int $tenantId, int $anoLetivoId): int
    {
        // Conta turmas/disciplinas do professor que não têm NENHUMA nota lançada no período ativo
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare(
            'SELECT COUNT(DISTINCT CONCAT(pdt.turma_id, "-", pdt.disciplina_id)) as total
             FROM professor_disciplina_turma pdt
             INNER JOIN turmas t ON t.id = pdt.turma_id
             WHERE pdt.user_id = ? AND pdt.tenant_id = ? AND t.ano_letivo_id = ? AND t.ativo = 1
               AND NOT EXISTS (
                 SELECT 1 FROM notas n
                 INNER JOIN avaliacoes av ON av.id = n.avaliacao_id
                 WHERE av.turma_id = pdt.turma_id
                   AND av.disciplina_id = pdt.disciplina_id
                   AND n.tenant_id = pdt.tenant_id
                   AND n.lancado_por = pdt.user_id
               )'
        );
        $stmt->execute([$userId, $tenantId, $anoLetivoId]);
        return (int) ($stmt->fetchColumn() ?? 0);
    }

    private function getAlunosAtencaoProfessor(int $userId, int $tenantId): int
    {
        // Conta alunos com pelo menos 1 falta nas turmas do professor
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare(
            'SELECT COUNT(DISTINCT f.aluno_id) as total
             FROM frequencias f
             INNER JOIN aulas a ON a.id = f.aula_id
             INNER JOIN professor_disciplina_turma pdt
               ON pdt.turma_id = a.turma_id
               AND pdt.disciplina_id = a.disciplina_id
               AND pdt.user_id = ?
               AND pdt.tenant_id = ?
             WHERE f.presente = 0'
        );
        $stmt->execute([$userId, $tenantId]);
        return (int) ($stmt->fetchColumn() ?? 0);
    }

    private function getTurmasProfessor(int $userId, int $tenantId): array
    {
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare(
            'SELECT DISTINCT t.id, t.nome, t.serie, t.turno,
                    COUNT(DISTINCT m.aluno_id) as total_alunos
             FROM turmas t
             INNER JOIN professor_disciplina_turma pdt ON pdt.turma_id = t.id
             LEFT JOIN matriculas m ON m.turma_id = t.id AND m.status = "ativo"
             WHERE pdt.user_id = ? AND pdt.tenant_id = ? AND t.ativo = 1
             GROUP BY t.id
             ORDER BY t.nome ASC'
        );
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }
}
