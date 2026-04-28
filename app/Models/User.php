<?php

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email, int $tenantId): ?array
    {
        return $this->findOneBy(['email' => $email, 'ativo' => 1], $tenantId);
    }

    public function findByCpf(string $cpf, int $tenantId): ?array
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return $this->findOneBy(['cpf' => $cpf, 'ativo' => 1], $tenantId);
    }

    public function authenticate(string $email, string $password, int $tenantId): ?array
    {
        $user = $this->findByEmail($email, $tenantId);
        if (!$user) return null;
        if (!password_verify($password, $user['senha_hash'])) return null;
        return $user;
    }

    public function create(array $data): int
    {
        if (isset($data['senha'])) {
            $data['senha_hash'] = password_hash($data['senha'], PASSWORD_BCRYPT, ['cost' => 12]);
            unset($data['senha']);
        }
        return parent::create($data);
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, [
            'senha_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])
        ]);
    }

    public function updateLastAccess(int $userId): void
    {
        $this->query('UPDATE users SET ultimo_acesso = ? WHERE id = ?', [now(), $userId]);
    }

    public function updateTheme(int $userId, string $theme): void
    {
        $this->query('UPDATE users SET tema_preferido = ? WHERE id = ?', [$theme, $userId]);
    }

    public function byRole(string $role, int $tenantId): array
    {
        return $this->findBy(['papel' => $role, 'ativo' => 1], $tenantId, 'nome ASC');
    }

    /** Remove dados sensíveis antes de expor */
    public static function sanitize(array $user): array
    {
        unset($user['senha_hash'], $user['2fa_segredo']);
        return $user;
    }
}
