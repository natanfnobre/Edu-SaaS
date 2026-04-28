<?php

namespace App\Models;

class Tenant extends BaseModel
{
    protected string $table = 'tenants';
    protected bool $tenantScoped = false; // Tenant não filtra por si mesmo

    public function findBySlug(string $slug): ?array
    {
        return $this->findOneBy(['slug' => $slug, 'ativo' => 1]);
    }

    public function findByDomain(string $domain): ?array
    {
        return $this->findOneBy(['dominio_personalizado' => $domain, 'ativo' => 1]);
    }

    public function getVisual(int $tenantId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM tenant_visual WHERE tenant_id = ? LIMIT 1',
            [$tenantId]
        );
        return $stmt->fetch() ?: null;
    }

    public function getAcademico(int $tenantId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM tenant_academico WHERE tenant_id = ? LIMIT 1',
            [$tenantId]
        );
        return $stmt->fetch() ?: null;
    }

    public function updateVisual(int $tenantId, array $data): bool
    {
        $existing = $this->getVisual($tenantId);
        if ($existing) {
            $set    = implode(' = ?, ', array_keys($data)) . ' = ?';
            $params = array_values($data);
            $params[] = $tenantId;
            return $this->query("UPDATE tenant_visual SET {$set} WHERE tenant_id = ?", $params)->rowCount() > 0;
        } else {
            $data['tenant_id'] = $tenantId;
            $cols    = implode(', ', array_keys($data));
            $places  = implode(', ', array_fill(0, count($data), '?'));
            $this->query("INSERT INTO tenant_visual ({$cols}) VALUES ({$places})", array_values($data));
            return true;
        }
    }

    public function updateAcademico(int $tenantId, array $data): bool
    {
        $existing = $this->getAcademico($tenantId);
        if ($existing) {
            $set    = implode(' = ?, ', array_keys($data)) . ' = ?';
            $params = array_values($data);
            $params[] = $tenantId;
            return $this->query("UPDATE tenant_academico SET {$set} WHERE tenant_id = ?", $params)->rowCount() > 0;
        } else {
            $data['tenant_id'] = $tenantId;
            $cols    = implode(', ', array_keys($data));
            $places  = implode(', ', array_fill(0, count($data), '?'));
            $this->query("INSERT INTO tenant_academico ({$cols}) VALUES ({$places})", array_values($data));
            return true;
        }
    }

    /** Gera um slug único baseado no nome */
    public function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        $base = $slug;
        $i    = 1;

        while ($this->findOneBy(['slug' => $slug])) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
