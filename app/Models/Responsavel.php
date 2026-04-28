<?php

namespace App\Models;

class Responsavel extends BaseModel
{
    protected string $table = 'responsaveis';

    public function porAluno(int $alunoId, int $tenantId): array
    {
        return $this->findBy(['aluno_id' => $alunoId, 'ativo' => 1], $tenantId, 'contato_emergencia DESC, id ASC');
    }

    public function withEndereco(int $responsavelId, int $tenantId): ?array
    {
        $resp = $this->findById($responsavelId, $tenantId);
        if (!$resp) return null;

        $stmt = $this->query(
            'SELECT * FROM enderecos WHERE entidade_tipo = "responsavel" AND entidade_id = ? LIMIT 1',
            [$responsavelId]
        );
        $resp['endereco'] = $stmt->fetch() ?: null;

        return $resp;
    }

    public function createWithSenha(array $dados): int
    {
        // Gera senha padrão: últimos 4 dígitos CPF + @slug-escola
        if (empty($dados['senha_portal_hash']) && !empty($dados['cpf']) && !empty($dados['tenant_id'])) {
            $cpfLimpo = preg_replace('/[^0-9]/', '', $dados['cpf']);
            $ultimos4 = substr($cpfLimpo, -4);
            
            // Busca slug da escola
            $stmt = $this->query('SELECT slug FROM tenants WHERE id = ? LIMIT 1', [$dados['tenant_id']]);
            $tenant = $stmt->fetch();
            $slug   = $tenant ? $tenant['slug'] : 'escola';

            $senhaDefault = "{$ultimos4}@{$slug}";
            $dados['senha_portal_hash'] = password_hash($senhaDefault, PASSWORD_BCRYPT, ['cost' => 12]);
            $dados['trocar_senha'] = 1;
        }

        return $this->create($dados);
    }

    public function createWithEndereco(array $dadosResp, ?array $dadosEnd = null): int
    {
        return $this->transaction(function () use ($dadosResp, $dadosEnd) {
            $respId = $this->createWithSenha($dadosResp);

            if ($dadosEnd) {
                $dadosEnd['entidade_tipo'] = 'responsavel';
                $dadosEnd['entidade_id'] = $respId;
                $this->query(
                    'INSERT INTO enderecos (entidade_tipo, entidade_id, logradouro, numero, complemento, bairro, cidade, estado, cep) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $dadosEnd['entidade_tipo'],
                        $dadosEnd['entidade_id'],
                        $dadosEnd['logradouro'] ?? null,
                        $dadosEnd['numero'] ?? null,
                        $dadosEnd['complemento'] ?? null,
                        $dadosEnd['bairro'] ?? null,
                        $dadosEnd['cidade'] ?? null,
                        $dadosEnd['estado'] ?? null,
                        $dadosEnd['cep'] ?? null,
                    ]
                );
            }

            return $respId;
        });
    }

    public function resetarSenha(int $responsavelId, int $tenantId): bool
    {
        $resp = $this->findById($responsavelId, $tenantId);
        if (!$resp) return false;

        $cpfLimpo = preg_replace('/[^0-9]/', '', $resp['cpf'] ?? '');
        $ultimos4 = substr($cpfLimpo, -4);
        
        $stmt = $this->query('SELECT slug FROM tenants WHERE id = ? LIMIT 1', [$tenantId]);
        $tenant = $stmt->fetch();
        $slug   = $tenant ? $tenant['slug'] : 'escola';

        $senhaDefault = "{$ultimos4}@{$slug}";

        return $this->update($responsavelId, [
            'senha_portal_hash' => password_hash($senhaDefault, PASSWORD_BCRYPT, ['cost' => 12]),
            'trocar_senha' => 1
        ], $tenantId);
    }
}
