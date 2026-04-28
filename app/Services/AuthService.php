<?php

namespace App\Services;

use App\Models\User;
use App\Models\LogAuditoria;

class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function attempt(string $email, string $password, int $tenantId): bool
    {
        $user = $this->userModel->authenticate($email, $password, $tenantId);
        if (!$user) return false;

        $this->startSession($user, $tenantId);
        return true;
    }

    /** Login para pais/responsáveis (identificador diferente) */
    public function attemptPai(string $identifier, string $password, int $tenantId): bool
    {
        // Responsáveis usam CPF ou username como identificador
        $stmt = (new \App\Helpers\Database)::getInstance()->prepare(
            'SELECT * FROM responsaveis WHERE tenant_id = ? AND (cpf = ? OR email = ?) AND ativo = 1 LIMIT 1'
        );
        $cpf  = preg_replace('/[^0-9]/', '', $identifier);
        $stmt->execute([$tenantId, $cpf, $identifier]);
        $responsavel = $stmt->fetch();

        if (!$responsavel) return false;
        if (!password_verify($password, $responsavel['senha_portal_hash'])) return false;

        // Sessão de pai é separada
        $_SESSION['pai_id']    = $responsavel['id'];
        $_SESSION['tenant_id'] = $tenantId;
        $_SESSION['papel']     = 'pai';
        $_SESSION['nome']      = $responsavel['nome_completo'];

        return true;
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public function check(): bool
    {
        return !empty($_SESSION['user_id']) || !empty($_SESSION['pai_id']);
    }

    public function user(): ?array
    {
        if (empty($_SESSION['user_id'])) return null;
        return $this->userModel->findById((int) $_SESSION['user_id']);
    }

    public function role(): ?string
    {
        return $_SESSION['papel'] ?? null;
    }

    public function tenantId(): ?int
    {
        return isset($_SESSION['tenant_id']) ? (int) $_SESSION['tenant_id'] : null;
    }

    public function can(string $permission): bool
    {
        $role = $this->role();
        if (!$role) return false;

        $roles = require CONFIG_PATH . '/roles.php';
        $allowed = $roles['permissions'][$permission] ?? [];

        return in_array($role, $allowed);
    }

    public function hasRole(string|array $roles): bool
    {
        $current = $this->role();
        if (!$current) return false;

        return in_array($current, (array) $roles);
    }

    private function startSession(array $user, int $tenantId): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['tenant_id'] = $tenantId;
        $_SESSION['papel']     = $user['papel'];
        $_SESSION['nome']      = $user['nome'];
        $_SESSION['tema']      = $user['tema_preferido'] ?? 'sistema';

        $this->userModel->updateLastAccess($user['id']);
    }
}
