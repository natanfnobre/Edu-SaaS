<?php

namespace App\Models;

class LogAuditoria extends BaseModel
{
    protected string $table = 'logs_auditoria';

    public function registrar(string $acao, string $entidadeTipo, int $entidadeId, ?array $antes = null, ?array $depois = null): void
    {
        $userId   = $_SESSION['user_id'] ?? null;
        $tenantId = $_SESSION['tenant_id'] ?? null;

        $this->create([
            'tenant_id'      => $tenantId,
            'user_id'        => $userId,
            'acao'           => $acao,
            'entidade_tipo'  => $entidadeTipo,
            'entidade_id'    => $entidadeId,
            'dados_antes'    => $antes ? json_encode($antes) : null,
            'dados_depois'   => $depois ? json_encode($depois) : null,
            'ip'             => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    public function porEntidade(string $tipo, int $id, int $tenantId): array
    {
        $stmt = $this->query(
            'SELECT l.*, u.nome as user_nome FROM logs_auditoria l
             LEFT JOIN users u ON u.id = l.user_id
             WHERE l.tenant_id = ? AND l.entidade_tipo = ? AND l.entidade_id = ?
             ORDER BY l.criado_em DESC',
            [$tenantId, $tipo, $id]
        );
        return $stmt->fetchAll();
    }

    public function porUsuario(int $userId, int $tenantId, int $limit = 50): array
    {
        $stmt = $this->query(
            'SELECT * FROM logs_auditoria WHERE tenant_id = ? AND user_id = ?
             ORDER BY criado_em DESC LIMIT ?',
            [$tenantId, $userId, $limit]
        );
        return $stmt->fetchAll();
    }

    public function recentes(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->query(
            'SELECT l.*, u.nome as user_nome FROM logs_auditoria l
             LEFT JOIN users u ON u.id = l.user_id
             WHERE l.tenant_id = ?
             ORDER BY l.criado_em DESC LIMIT ?',
            [$tenantId, $limit]
        );
        return $stmt->fetchAll();
    }
}
