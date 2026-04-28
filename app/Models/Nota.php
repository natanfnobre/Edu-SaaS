<?php

namespace App\Models;

class Nota extends BaseModel
{
    protected string $table = 'notas';

    /**
     * Salva notas em lote usando UPSERT (INSERT ... ON DUPLICATE KEY UPDATE).
     * Garante que o professor pode corrigir sem gerar duplicatas.
     *
     * @param array $lote  Array de ['avaliacao_id' => int, 'aluno_id' => int, 'nota' => float|null, 'obs' => string|null]
     */
    public function salvarLote(array $lote, int $lancadoPor, int $tenantId): void
    {
        if (empty($lote)) return;

        $this->transaction(function () use ($lote, $lancadoPor, $tenantId) {
            foreach ($lote as $item) {
                // Nota vazia = pula
                if ($item['nota'] === '' || $item['nota'] === null) {
                    continue;
                }

                $this->query(
                    'INSERT INTO notas
                        (tenant_id, avaliacao_id, aluno_id, nota, observacao, lancado_por, lancado_em)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE
                        nota        = VALUES(nota),
                        observacao  = VALUES(observacao),
                        editado_por = VALUES(lancado_por),
                        editado_em  = NOW()',
                    [
                        $tenantId,
                        $item['avaliacao_id'],
                        $item['aluno_id'],
                        (float) $item['nota'],
                        $item['obs'] ?? null,
                        $lancadoPor,
                    ]
                );
            }
        });
    }

    /**
     * Retorna todas as notas de um aluno em uma turma, organizadas por período e avaliação.
     */
    public function porAlunoETurma(int $alunoId, int $turmaId, int $tenantId): array
    {
        return $this->query(
            'SELECT n.*, av.nome as avaliacao_nome, av.peso, av.nota_maxima, av.ordem,
                    d.nome as disciplina_nome, d.cor_icone,
                    p.nome as periodo_nome, p.numero as periodo_numero
             FROM notas n
             INNER JOIN avaliacoes av  ON av.id = n.avaliacao_id
             INNER JOIN disciplinas d  ON d.id  = av.disciplina_id
             INNER JOIN periodos p     ON p.id  = av.periodo_id
             WHERE n.aluno_id = ? AND av.turma_id = ? AND n.tenant_id = ?
             ORDER BY p.numero ASC, d.nome ASC, av.ordem ASC',
            [$alunoId, $turmaId, $tenantId]
        )->fetchAll();
    }

    /**
     * Retorna as notas de todos os alunos de uma turma para uma avaliação específica.
     * Usado na tela de lançamento em lote.
     */
    public function porAvaliacao(int $avaliacaoId, int $tenantId): array
    {
        $rows = $this->query(
            'SELECT aluno_id, nota, observacao FROM notas
             WHERE avaliacao_id = ? AND tenant_id = ?',
            [$avaliacaoId, $tenantId]
        )->fetchAll();

        // Indexa por aluno_id para acesso O(1)
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['aluno_id']] = $r;
        }
        return $indexed;
    }

    /**
     * Retorna todas as notas de uma turma em um período, para o lançamento em lote.
     * Retorna array indexado por [aluno_id][avaliacao_id].
     */
    public function porTurmaEPeriodo(int $turmaId, int $periodoId, int $tenantId): array
    {
        $rows = $this->query(
            'SELECT n.aluno_id, n.avaliacao_id, n.nota, n.observacao
             FROM notas n
             INNER JOIN avaliacoes av ON av.id = n.avaliacao_id
             WHERE av.turma_id = ? AND av.periodo_id = ? AND n.tenant_id = ?',
            [$turmaId, $periodoId, $tenantId]
        )->fetchAll();

        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['aluno_id']][$r['avaliacao_id']] = $r;
        }
        return $indexed;
    }
}
