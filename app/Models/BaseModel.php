<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;
use PDOStatement;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /** Se true, exige tenant_id em todas as queries */
    protected bool $tenantScoped = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Leitura ──────────────────────────────────────────────────

    public function findById(int $id, ?int $tenantId = null): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];

        if ($this->tenantScoped && $tenantId) {
            $sql .= ' AND tenant_id = ?';
            $params[] = $tenantId;
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findBy(array $conditions, ?int $tenantId = null, string $order = '', int $limit = 0): array
    {
        [$where, $params] = $this->buildWhere($conditions, $tenantId);

        $sql = "SELECT * FROM {$this->table} WHERE {$where}";
        if ($order)  $sql .= " ORDER BY {$order}";
        if ($limit)  $sql .= " LIMIT {$limit}";

        return $this->query($sql, $params)->fetchAll();
    }

    public function findOneBy(array $conditions, ?int $tenantId = null): ?array
    {
        $result = $this->findBy($conditions, $tenantId, limit: 1);
        return $result[0] ?? null;
    }

    public function all(?int $tenantId = null, string $order = 'id ASC', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if ($this->tenantScoped && $tenantId) {
            $sql .= ' WHERE tenant_id = ?';
            $params[] = $tenantId;
        }

        $sql .= " ORDER BY {$order}";
        if ($limit)  $sql .= " LIMIT {$limit} OFFSET {$offset}";

        return $this->query($sql, $params)->fetchAll();
    }

    public function count(array $conditions = [], ?int $tenantId = null): int
    {
        [$where, $params] = $this->buildWhere($conditions, $tenantId);
        $sql = "SELECT COUNT(*) FROM {$this->table}" . ($where ? " WHERE {$where}" : '');
        return (int) $this->query($sql, $params)->fetchColumn();
    }

    public function paginate(int $page, int $perPage, array $conditions = [], ?int $tenantId = null, string $order = 'id ASC'): array
    {
        $total  = $this->count($conditions, $tenantId);
        $offset = ($page - 1) * $perPage;
        $items  = $this->findBy($conditions, $tenantId, $order, $perPage);

        // Se offset > 0, precisamos fazer manualmente
        if ($offset > 0) {
            [$where, $params] = $this->buildWhere($conditions, $tenantId);
            $sql   = "SELECT * FROM {$this->table}" . ($where ? " WHERE {$where}" : '') . " ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}";
            $items = $this->query($sql, $params)->fetchAll();
        }

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    // ── Escrita ──────────────────────────────────────────────────

    public function create(array $data): int
    {
        // Remove campos internos
        unset($data['_csrf_token']);

        $data['criado_em'] = $data['criado_em'] ?? now();

        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_fill(0, count($data), '?'));

        $this->query("INSERT INTO {$this->table} ({$cols}) VALUES ({$places})", array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data, ?int $tenantId = null): bool
    {
        // Remove campos internos
        unset($data['_csrf_token']);

        $data['atualizado_em'] = now();

        $set    = implode(' = ?, ', array_keys($data)) . ' = ?';
        $params = array_values($data);
        $params[] = $id;

        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";

        if ($this->tenantScoped && $tenantId) {
            $sql .= ' AND tenant_id = ?';
            $params[] = $tenantId;
        }

        return $this->query($sql, $params)->rowCount() > 0;
    }

    public function delete(int $id, ?int $tenantId = null): bool
    {
        $sql    = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];

        if ($this->tenantScoped && $tenantId) {
            $sql .= ' AND tenant_id = ?';
            $params[] = $tenantId;
        }

        return $this->query($sql, $params)->rowCount() > 0;
    }

    /** Soft delete — seta ativo = 0 */
    public function softDelete(int $id, ?int $tenantId = null): bool
    {
        return $this->update($id, ['ativo' => 0], $tenantId);
    }

    // ── Utilitários ──────────────────────────────────────────────

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function buildWhere(array $conditions, ?int $tenantId = null): array
    {
        $parts  = [];
        $params = [];

        if ($this->tenantScoped && $tenantId) {
            $parts[]  = 'tenant_id = ?';
            $params[] = $tenantId;
        }

        foreach ($conditions as $col => $val) {
            if (is_null($val)) {
                $parts[] = "{$col} IS NULL";
            } elseif (is_array($val)) {
                $places  = implode(', ', array_fill(0, count($val), '?'));
                $parts[] = "{$col} IN ({$places})";
                $params  = array_merge($params, $val);
            } else {
                $parts[]  = "{$col} = ?";
                $params[] = $val;
            }
        }

        return [implode(' AND ', $parts) ?: '1=1', $params];
    }

    /** Executa dentro de uma transação */
    public function transaction(callable $callback): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $callback($this);
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
