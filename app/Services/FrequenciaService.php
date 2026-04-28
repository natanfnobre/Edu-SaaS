<?php

namespace App\Services;

class FrequenciaService
{
    /**
     * Calcula o percentual de faltas de um aluno.
     *
     * @param int $totalAulas   Total de aulas registradas
     * @param int $totalFaltas  Número de faltas do aluno
     */
    public function calcularPercentualFaltas(int $totalAulas, int $totalFaltas): float
    {
        if ($totalAulas <= 0) {
            return 0.0;
        }
        return round(($totalFaltas / $totalAulas) * 100, 1);
    }

    /**
     * Status da frequência baseado no percentual de faltas.
     *
     * @param float $percentualFaltas  Ex: 20.0 = 20%
     * @param int   $limiteMaximo      Ex: 25 = máximo de 25% de faltas
     */
    public function statusFrequencia(float $percentualFaltas, int $limiteMaximo): string
    {
        if ($percentualFaltas >= $limiteMaximo) {
            return 'critico'; // Reprovado por falta
        }
        // Alerta quando atingir 75% do limite
        if ($percentualFaltas >= ($limiteMaximo * 0.75)) {
            return 'atencao';
        }
        return 'ok';
    }

    /**
     * Retorna a classe CSS e label para o status de frequência.
     */
    public static function badgeFrequencia(string $status): array
    {
        return match ($status) {
            'critico'  => ['class' => 'badge--danger',   'label' => 'Crítico'],
            'atencao'  => ['class' => 'badge--warning',  'label' => 'Atenção'],
            default    => ['class' => 'badge--success',  'label' => 'OK'],
        };
    }
}
