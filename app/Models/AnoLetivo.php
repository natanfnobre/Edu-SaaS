<?php

namespace App\Models;

class AnoLetivo extends BaseModel
{
    protected string $table = 'anos_letivos';

    public function ativo(int $tenantId): ?array
    {
        return $this->findOneBy(['ativo' => 1], $tenantId, 'data_inicio DESC');
    }

    public function withPeriodos(int $anoLetivoId, int $tenantId): ?array
    {
        $ano = $this->findById($anoLetivoId, $tenantId);
        if (!$ano) return null;

        $stmt = $this->query(
            'SELECT * FROM periodos WHERE ano_letivo_id = ? AND tenant_id = ? ORDER BY numero ASC',
            [$anoLetivoId, $tenantId]
        );
        $ano['periodos'] = $stmt->fetchAll();

        return $ano;
    }

    public function criarPeriodos(int $anoLetivoId, int $tenantId): void
    {
        // Busca configuração acadêmica da escola
        $stmt = $this->query('SELECT * FROM tenant_academico WHERE tenant_id = ? LIMIT 1', [$tenantId]);
        $config = $stmt->fetch();

        if (!$config) return;

        $tipo = $config['tipo_periodo']; // bimestre, trimestre, semestre
        $qtd  = (int) $config['qtd_periodos'];

        // Busca datas do ano letivo
        $ano = $this->findById($anoLetivoId, $tenantId);
        if (!$ano) return;

        $inicio = new \DateTime($ano['data_inicio']);
        $fim    = new \DateTime($ano['data_fim']);

        // Calcula duração de cada período
        $diasTotais = $inicio->diff($fim)->days;
        $diasPorPeriodo = floor($diasTotais / $qtd);

        for ($i = 1; $i <= $qtd; $i++) {
            $periodoInicio = clone $inicio;
            $periodoFim    = clone $inicio;
            $periodoFim->modify("+{$diasPorPeriodo} days");

            if ($i === $qtd) {
                // Último período vai até o fim do ano letivo
                $periodoFim = $fim;
            }

            $nomeTipo = match ($tipo) {
                'bimestre'  => "{$i}º Bimestre",
                'trimestre' => "{$i}º Trimestre",
                'semestre'  => "{$i}º Semestre",
                default     => "Período {$i}",
            };

            $this->query(
                'INSERT INTO periodos (tenant_id, ano_letivo_id, nome, numero, data_inicio, data_fim) 
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$tenantId, $anoLetivoId, $nomeTipo, $i, $periodoInicio->format('Y-m-d'), $periodoFim->format('Y-m-d')]
            );

            // Próximo período começa no dia seguinte
            $inicio = clone $periodoFim;
            $inicio->modify('+1 day');
        }
    }

    public function ativar(int $id, int $tenantId): void
    {
        $this->transaction(function() use ($id, $tenantId) {
            // Desativa todos do tenant
            $this->query('UPDATE anos_letivos SET ativo = 0 WHERE tenant_id = ?', [$tenantId]);
            // Ativa o selecionado
            $this->query('UPDATE anos_letivos SET ativo = 1 WHERE id = ? AND tenant_id = ?', [$id, $tenantId]);
        });
    }
}
