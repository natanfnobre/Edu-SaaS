<?php

namespace App\Services;

use App\Models\{Aluno, Nota, Avaliacao, Frequencia, Aula};

/**
 * Service para importação de Excel (notas e frequência)
 * 
 * Usa PhpSpreadsheet para ler .xlsx
 * Valida dados, faz fuzzy match de nomes, retorna preview
 */
class ExcelImportService
{
    private Aluno $alunoModel;
    private Nota $notaModel;
    private Avaliacao $avaliacaoModel;
    private Frequencia $frequenciaModel;
    private Aula $aulaModel;

    public function __construct()
    {
        $this->alunoModel = new Aluno();
        $this->notaModel = new Nota();
        $this->avaliacaoModel = new Avaliacao();
        $this->frequenciaModel = new Frequencia();
        $this->aulaModel = new Aula();
    }

    /**
     * Importa notas de arquivo Excel
     * 
     * @param array $file $_FILES['arquivo']
     * @param int $turmaId
     * @param int $disciplinaId  
     * @param int $periodoId
     * @param int $tenantId
     * @return array ['sucesso' => bool, 'preview' => array, 'erros' => array]
     */
    public function importarNotas(array $file, int $turmaId, int $disciplinaId, int $periodoId, int $tenantId): array
    {
        // Validação inicial
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['sucesso' => false, 'erros' => ['Erro no upload do arquivo']];
        }

        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, ['xlsx', 'xls'])) {
            return ['sucesso' => false, 'erros' => ['Apenas arquivos .xlsx ou .xls são permitidos']];
        }

        // Lê Excel (simulado - em prod usar PhpSpreadsheet)
        $dados = $this->lerExcel($file['tmp_name']);
        
        if (empty($dados)) {
            return ['sucesso' => false, 'erros' => ['Arquivo vazio ou formato inválido']];
        }

        // Busca alunos da turma
        $alunosDaTurma = $this->alunoModel->porTurma($turmaId, $tenantId, 1); // TODO: ano letivo dinâmico
        
        // Busca avaliações
        $avaliacoes = $this->avaliacaoModel->porTurmaDisciplinaPeriodo($turmaId, $disciplinaId, $periodoId, $tenantId);
        
        if (empty($avaliacoes)) {
            return ['sucesso' => false, 'erros' => ['Nenhuma avaliação configurada para esta turma/disciplina/período']];
        }

        // Valida estrutura do Excel
        $header = array_shift($dados); // Primeira linha = cabeçalho
        $erros = [];
        $preview = [];
        
        // Espera: Nome do Aluno | AV1 | AV2 | ...
        if (!isset($header[0]) || !stripos($header[0], 'aluno')) {
            $erros[] = 'Primeira coluna deve ser "Nome do Aluno"';
        }

        // Processa cada linha
        foreach ($dados as $linha => $valores) {
            $nomeAluno = trim($valores[0] ?? '');
            
            if (empty($nomeAluno)) continue;

            // Fuzzy match: encontra aluno por nome
            $aluno = $this->encontrarAluno($nomeAluno, $alunosDaTurma);
            
            if (!$aluno) {
                $erros[] = "Linha " . ($linha + 2) . ": Aluno '{$nomeAluno}' não encontrado na turma";
                continue;
            }

            // Processa notas das avaliações
            $notasLinha = [];
            foreach ($avaliacoes as $idx => $av) {
                $coluna = $idx + 1; // Colunas de nota começam na posição 1
                $notaTexto = trim($valores[$coluna] ?? '');
                
                if ($notaTexto === '' || $notaTexto === '--') {
                    continue; // Nota não preenchida
                }

                // Valida formato
                $nota = $this->parseNota($notaTexto);
                
                if ($nota === null) {
                    $erros[] = "Linha " . ($linha + 2) . ": Nota inválida '{$notaTexto}' para {$av['nome']}";
                    continue;
                }

                if ($nota < 0 || $nota > $av['nota_maxima']) {
                    $erros[] = "Linha " . ($linha + 2) . ": Nota {$nota} fora do intervalo 0-{$av['nota_maxima']} em {$av['nome']}";
                    continue;
                }

                $notasLinha[$av['id']] = $nota;
            }

            $preview[] = [
                'aluno_id' => $aluno['id'],
                'aluno_nome' => $aluno['nome_completo'],
                'notas' => $notasLinha,
            ];
        }

        return [
            'sucesso' => empty($erros) || !empty($preview),
            'preview' => $preview,
            'erros' => $erros,
            'total_linhas' => count($dados),
            'total_alunos' => count($preview),
        ];
    }

    /**
     * Confirma importação salvando no banco
     */
    public function confirmarImportacao(array $preview, int $tenantId, int $usuarioId): array
    {
        $salvos = 0;
        $erros = [];

        foreach ($preview as $item) {
            foreach ($item['notas'] as $avaliacaoId => $nota) {
                try {
                    // Verifica se já existe
                    $existing = $this->notaModel->findOneBy([
                        'aluno_id' => $item['aluno_id'],
                        'avaliacao_id' => $avaliacaoId,
                    ], $tenantId);

                    if ($existing) {
                        // Atualiza
                        $this->notaModel->update($existing['id'], [
                            'nota' => $nota,
                            'editado_por' => $usuarioId,
                            'editado_em' => now(),
                        ], $tenantId);
                    } else {
                        // Cria
                        $this->notaModel->create([
                            'tenant_id' => $tenantId,
                            'avaliacao_id' => $avaliacaoId,
                            'aluno_id' => $item['aluno_id'],
                            'nota' => $nota,
                            'lancado_por' => $usuarioId,
                        ]);
                    }
                    
                    $salvos++;
                } catch (\Exception $e) {
                    $erros[] = "Erro ao salvar nota de {$item['aluno_nome']}: " . $e->getMessage();
                }
            }
        }

        return [
            'sucesso' => $salvos > 0,
            'total_salvos' => $salvos,
            'erros' => $erros,
        ];
    }

    /**
     * Gera Excel modelo para download
     */
    public function gerarModelo(int $turmaId, int $disciplinaId, int $periodoId, int $tenantId): string
    {
        $alunos = $this->alunoModel->porTurma($turmaId, $tenantId, 1);
        $avaliacoes = $this->avaliacaoModel->porTurmaDisciplinaPeriodo($turmaId, $disciplinaId, $periodoId, $tenantId);

        // Gera CSV simples (em prod usar PhpSpreadsheet para .xlsx)
        $csv = "Nome do Aluno";
        foreach ($avaliacoes as $av) {
            $csv .= "," . $av['nome'];
        }
        $csv .= "\n";

        foreach ($alunos as $aluno) {
            $csv .= $aluno['nome_completo'];
            foreach ($avaliacoes as $av) {
                $csv .= ","; // Vazio para preencher
            }
            $csv .= "\n";
        }

        // Salva temporariamente
        $filename = 'modelo-notas-' . time() . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($filepath, $csv);

        return $filepath;
    }

    // ── Helpers privados ──────────────────────────────────────────

    /**
     * Lê arquivo Excel (simplificado - usar PhpSpreadsheet em produção)
     */
    private function lerExcel(string $filepath): array
    {
        // Simulação: lê como CSV
        // Em produção: usar PhpOffice\PhpSpreadsheet
        
        $dados = [];
        if (($handle = fopen($filepath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $dados[] = $row;
            }
            fclose($handle);
        }
        
        return $dados;
    }

    /**
     * Encontra aluno por nome (fuzzy match)
     */
    private function encontrarAluno(string $nome, array $alunos): ?array
    {
        $nome = $this->normalizarNome($nome);
        
        // Busca exata
        foreach ($alunos as $aluno) {
            if ($this->normalizarNome($aluno['nome_completo']) === $nome) {
                return $aluno;
            }
        }

        // Busca parcial (primeiro e último nome)
        $partes = explode(' ', $nome);
        if (count($partes) >= 2) {
            $primeiro = $partes[0];
            $ultimo = end($partes);
            
            foreach ($alunos as $aluno) {
                $nomeAluno = $this->normalizarNome($aluno['nome_completo']);
                if (str_contains($nomeAluno, $primeiro) && str_contains($nomeAluno, $ultimo)) {
                    return $aluno;
                }
            }
        }

        return null;
    }

    /**
     * Normaliza nome para comparação
     */
    private function normalizarNome(string $nome): string
    {
        $nome = mb_strtolower(trim($nome));
        $nome = preg_replace('/\s+/', ' ', $nome); // Remove espaços duplos
        
        // Remove acentos
        $acentos = ['á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç'];
        $sem = ['a','a','a','a','e','e','i','o','o','o','u','u','c'];
        $nome = str_replace($acentos, $sem, $nome);
        
        return $nome;
    }

    /**
     * Converte string para nota (aceita vírgula ou ponto)
     */
    private function parseNota(string $texto): ?float
    {
        $texto = trim(str_replace(',', '.', $texto));
        
        if (!is_numeric($texto)) {
            return null;
        }
        
        return (float) $texto;
    }
}
