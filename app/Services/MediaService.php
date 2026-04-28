<?php

namespace App\Services;

class MediaService
{
    /**
     * Calcula a média de acordo com a fórmula configurada pelo tenant.
     *
     * @param array  $notas   Array de ['nota' => float|null, 'peso' => float]
     * @param string $formula 'simples' | 'ponderada'
     * @return float|null  Null se não há nenhuma nota lançada
     */
    public function calcularMedia(array $notas, string $formula = 'simples'): ?float
    {
        // Remove notas não lançadas (null)
        $notas = array_filter($notas, fn($n) => !is_null($n['nota']));

        if (empty($notas)) {
            return null;
        }

        $notas = array_values($notas);

        if ($formula === 'ponderada') {
            $somaPesos = array_sum(array_column($notas, 'peso'));
            if ($somaPesos <= 0) {
                return $this->calcularMedia($notas, 'simples');
            }
            $soma = 0.0;
            foreach ($notas as $n) {
                $soma += (float) $n['nota'] * (float) $n['peso'];
            }
            return round($soma / $somaPesos, 2);
        }

        // Simples: média aritmética
        $soma = array_sum(array_column($notas, 'nota'));
        return round($soma / count($notas), 2);
    }

    /**
     * Retorna o status final do aluno com base na média.
     */
    public function statusAluno(?float $media, float $notaMinima): string
    {
        if ($media === null) {
            return 'pendente';
        }
        if ($media >= $notaMinima) {
            return 'aprovado';
        }
        // Convenção: abaixo da nota mínima mas acima da metade → recuperação
        if ($media >= ($notaMinima / 2)) {
            return 'recuperacao';
        }
        return 'reprovado';
    }

    /**
     * Retorna a classe CSS e o label para o status do aluno.
     */
    public static function badgeStatus(string $status): array
    {
        return match ($status) {
            'aprovado'    => ['class' => 'badge--success',  'label' => 'Aprovado'],
            'recuperacao' => ['class' => 'badge--warning',  'label' => 'Recuperação'],
            'reprovado'   => ['class' => 'badge--danger',   'label' => 'Reprovado'],
            default       => ['class' => 'badge--secondary','label' => 'Pendente'],
        };
    }
}
