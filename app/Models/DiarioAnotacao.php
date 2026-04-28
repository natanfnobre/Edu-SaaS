<?php

namespace App\Models;

class DiarioAnotacao extends BaseModel
{
    protected string $table = 'diario_anotacoes';

    /**
     * Busca todas as anotações visíveis de um aluno baseado no Papel de quem requisita
     * 
     * @param int $alunoId
     * @param int $tenantId
     * @param array $usuarioLogado ['id' => X, 'papel' => 'coordenador', ...]
     * @return array
     */
    public function getAnotacoesTimeline(int $alunoId, int $tenantId, array $usuarioLogado): array
    {
        $role = strtolower($usuarioLogado['papel']);
        $userId = (int) $usuarioLogado['id'];

        // Definição da regra de visibilidade do SELECT
        if (in_array($role, ['super_admin', 'diretor', 'coordenador', 'secretaria'])) {
            // A Equipe Administrativa geralmente vê todas, exceto as exclusivas 'somente_autor' de outros
            $clausulaVisibilidade = "d.visibilidade != 'somente_autor' OR d.autor_id = ?";
        } elseif ($role === 'professor') {
            // O professor pode ver o que escreveu, e tudo classificado como 'professores' ou 'todos'
            $clausulaVisibilidade = "d.autor_id = ? OR d.visibilidade IN ('professores', 'todos')";
        } elseif ($role === 'responsavel') {
             // Responsáveis ou Pais  podem ver 'pais' ou 'todos' (isso será ampliado na tela de pai amanhã)
            $clausulaVisibilidade = "d.visibilidade IN ('pais', 'todos')";
        } else {
            $clausulaVisibilidade = "d.visibilidade = 'todos'"; // Fallback restritivo generalista
        }

        $sql = "
            SELECT 
                d.*,
                u.nome as autor_nome, u.papel as autor_papel, u.foto_path as autor_foto
            FROM {$this->table} d
            INNER JOIN users u ON u.id = d.autor_id
            WHERE d.tenant_id = ? AND d.aluno_id = ? AND ($clausulaVisibilidade)
            ORDER BY d.criado_em DESC
        ";

        // Parâmetros de prepare
        $params = [];
        $params[] = $tenantId;
        $params[] = $alunoId;
        if (str_contains($clausulaVisibilidade, '?')) {
            $params[] = $userId;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
}
