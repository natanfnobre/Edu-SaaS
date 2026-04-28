<?php

namespace App\Services;

use App\Models\{Avaliacao, Nota, NotaRecuperacao};

class RecuperacaoService
{
    private NotaRecuperacao $notaRecuperacaoModel;
    private MediaService $mediaService;

    public function __construct()
    {
        $this->notaRecuperacaoModel = new NotaRecuperacao();
        $this->mediaService = new MediaService();
    }

    /**
     * Processa e salva as notas de recuperação enviadas pelo array
     * verificando a regra de negócio da Escola se a nota atual excede a anterior.
     */
    public function salvarNotasLote(
        int $tenantId,
        int $periodoRecId,
        int $turmaId,
        int $disciplinaId,
        int $periodoReferenciaId,
        array $notasBrutas,
        int $userId
    ): void {
        $avaliacoes = (new Avaliacao())->porPeriodo($tenantId, $turmaId, $disciplinaId, $periodoReferenciaId);
        $todasNotasBase = (new Nota())->porPeriodoTurma($tenantId, $periodoReferenciaId, $disciplinaId, $turmaId);
        
        $notasProcessadas = [];

        foreach ($notasBrutas as $alunoId => $valor) {
            if ($valor === '' || $valor === null) {
                continue; // Pula vazias
            }

            $notaInserida = (float) str_replace(',', '.', $valor);

            // Obtém as notas base do aluno para calcular a média antiga
            $notasAluno = $todasNotasBase[$alunoId] ?? [];
            $notasParaMedia = [];
            foreach ($avaliacoes as $av) {
                $n = $notasAluno[$av['id']]['nota'] ?? null;
                $notasParaMedia[] = ['nota' => $n, 'peso' => $av['peso']];
            }
            
            // Usaremos média simples (padrão) - para casos complexos poderíamos ler a formula do tenant_academico
            $mediaFormatada = $this->mediaService->calcularMedia('simples', $notasParaMedia);
            
            $notaAnterior = null;
            if ($mediaFormatada !== null && $mediaFormatada !== '—') {
                $notaAnterior = (float) str_replace(',', '.', $mediaFormatada);
            }

            // Regra Híbrida: a substituição costuma validar se a nota da recuperação supera a nota BIMESTRAL anterior.
            $substituiu = false;
            if ($notaAnterior !== null && $notaInserida > $notaAnterior) {
                $substituiu = true;
            } else if ($notaAnterior === null) {
                // Se o aluno simplesmente não tinha nota (faltou a tudo), a recuperação conta normal
                $substituiu = true; 
            }

            $notasProcessadas[$alunoId] = [
                'nota' => $notaInserida,
                'nota_substituiu' => $substituiu,
                'nota_anterior' => $notaAnterior
            ];
        }

        // Salvar tudo em base
        if (!empty($notasProcessadas)) {
            $this->notaRecuperacaoModel->salvarLote(
                $tenantId, 
                $periodoRecId, 
                $turmaId, 
                $disciplinaId, 
                $notasProcessadas, 
                $userId
            );
        }
    }
}
