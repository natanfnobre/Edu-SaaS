<?php

namespace App\Models;

class Aluno extends BaseModel
{
    protected string $table = 'alunos';

    public function withResponsaveis(int $alunoId, int $tenantId): ?array
    {
        $aluno = $this->findById($alunoId, $tenantId);
        if (!$aluno) return null;

        // Busca responsáveis
        $stmt = $this->query(
            'SELECT * FROM responsaveis WHERE aluno_id = ? AND tenant_id = ? AND ativo = 1 ORDER BY contato_emergencia DESC',
            [$alunoId, $tenantId]
        );
        $aluno['responsaveis'] = $stmt->fetchAll();

        // Busca endereço
        $stmt = $this->query(
            'SELECT * FROM enderecos WHERE entidade_tipo = "aluno" AND entidade_id = ? LIMIT 1',
            [$alunoId]
        );
        $aluno['endereco'] = $stmt->fetch() ?: null;

        return $aluno;
    }

    public function search(string $termo, int $tenantId, int $limit = 50): array
    {
        $termo = "%{$termo}%";
        $stmt = $this->query(
            'SELECT * FROM alunos 
             WHERE tenant_id = ? AND ativo = 1 
             AND (nome_completo LIKE ? OR cpf LIKE ? OR rg LIKE ?)
             ORDER BY nome_completo ASC
             LIMIT ?',
            [$tenantId, $termo, $termo, $termo, $limit]
        );
        return $stmt->fetchAll();
    }

    public function porTurma(int $turmaId, int $tenantId, int $anoLetivoId): array
    {
        $stmt = $this->query(
            'SELECT a.*, m.numero_matricula, m.data_matricula, m.status as status_matricula
             FROM alunos a
             INNER JOIN matriculas m ON m.aluno_id = a.id
             WHERE m.turma_id = ? AND m.ano_letivo_id = ? AND a.tenant_id = ? AND a.ativo = 1
             ORDER BY a.nome_completo ASC',
            [$turmaId, $anoLetivoId, $tenantId]
        );
        return $stmt->fetchAll();
    }

    public function turmasDoAluno(int $alunoId, int $tenantId): array
    {
        $stmt = $this->query(
            'SELECT t.*, m.numero_matricula, m.status, al.nome as ano_letivo
             FROM matriculas m
             INNER JOIN turmas t ON t.id = m.turma_id
             INNER JOIN anos_letivos al ON al.id = m.ano_letivo_id
             WHERE m.aluno_id = ? AND m.tenant_id = ?
             ORDER BY al.data_inicio DESC',
            [$alunoId, $tenantId]
        );
        return $stmt->fetchAll();
    }

    public function createWithEndereco(array $dadosAluno, ?array $dadosEndereco = null): int
    {
        return $this->transaction(function () use ($dadosAluno, $dadosEndereco) {
            $alunoId = $this->create($dadosAluno);

            if ($dadosEndereco) {
                $dadosEndereco['entidade_tipo'] = 'aluno';
                $dadosEndereco['entidade_id'] = $alunoId;
                $this->query(
                    'INSERT INTO enderecos (entidade_tipo, entidade_id, logradouro, numero, complemento, bairro, cidade, estado, cep) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $dadosEndereco['entidade_tipo'],
                        $dadosEndereco['entidade_id'],
                        $dadosEndereco['logradouro'] ?? null,
                        $dadosEndereco['numero'] ?? null,
                        $dadosEndereco['complemento'] ?? null,
                        $dadosEndereco['bairro'] ?? null,
                        $dadosEndereco['cidade'] ?? null,
                        $dadosEndereco['estado'] ?? null,
                        $dadosEndereco['cep'] ?? null,
                    ]
                );
            }

            return $alunoId;
        });
    }

    public function updateWithEndereco(int $alunoId, array $dadosAluno, ?array $dadosEndereco, int $tenantId): bool
    {
        return $this->transaction(function () use ($alunoId, $dadosAluno, $dadosEndereco, $tenantId) {
            $this->update($alunoId, $dadosAluno, $tenantId);

            if ($dadosEndereco) {
                // Verifica se já existe endereço
                $stmt = $this->query(
                    'SELECT id FROM enderecos WHERE entidade_tipo = "aluno" AND entidade_id = ? LIMIT 1',
                    [$alunoId]
                );
                $existing = $stmt->fetch();

                if ($existing) {
                    $this->query(
                        'UPDATE enderecos SET logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, cep = ?
                         WHERE entidade_tipo = "aluno" AND entidade_id = ?',
                        [
                            $dadosEndereco['logradouro'] ?? null,
                            $dadosEndereco['numero'] ?? null,
                            $dadosEndereco['complemento'] ?? null,
                            $dadosEndereco['bairro'] ?? null,
                            $dadosEndereco['cidade'] ?? null,
                            $dadosEndereco['estado'] ?? null,
                            $dadosEndereco['cep'] ?? null,
                            $alunoId
                        ]
                    );
                } else {
                    $this->query(
                        'INSERT INTO enderecos (entidade_tipo, entidade_id, logradouro, numero, complemento, bairro, cidade, estado, cep) 
                         VALUES ("aluno", ?, ?, ?, ?, ?, ?, ?, ?)',
                        [
                            $alunoId,
                            $dadosEndereco['logradouro'] ?? null,
                            $dadosEndereco['numero'] ?? null,
                            $dadosEndereco['complemento'] ?? null,
                            $dadosEndereco['bairro'] ?? null,
                            $dadosEndereco['cidade'] ?? null,
                            $dadosEndereco['estado'] ?? null,
                            $dadosEndereco['cep'] ?? null,
                        ]
                    );
                }
            }

            return true;
        });
    }
}
