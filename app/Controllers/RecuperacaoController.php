<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf, Validator};
use App\Models\{PeriodoRecuperacao, NotaRecuperacao, Turma, Disciplina, Periodo, Aluno, AnoLetivo};
use App\Services\RecuperacaoService;

class RecuperacaoController
{
    /**
     * Dashboard / Index da Recuperação.
     * Lista os períodos ativos pela Coordenação. Para o professor lista a oportunidade de lançar notas pelas turmas dele..
     */
    public function index()
    {
        $tenantId = currentUser()['tenant_id'];
        $anoAtivo = (new AnoLetivo())->ativo($tenantId);

        if (!$anoAtivo) {
            Flash::warning('Nenhum ano letivo ativo encontrado.');
            return View::render('recuperacao/index', ['periodos' => [], 'anoAtivo' => null]);
        }

        $periodoModel = new PeriodoRecuperacao();
        $periodos = $periodoModel->listarPorAnoLetivo($tenantId, $anoAtivo['id']);

        return View::render('recuperacao/index', [
            'periodos' => $periodos,
            'anoAtivo' => $anoAtivo,
            'isCoordenacao' => in_array(currentUser()['papel'], ['coordenador', 'diretor', 'super_admin'])
        ]);
    }

    /**
     * POST para o Coordenador abrir ou Editar um período.
     */
    public function salvarPeriodo()
    {
        if (!in_array(currentUser()['papel'], ['coordenador', 'diretor', 'super_admin'])) {
            Flash::error('Sem permissão.');
            return header('Location: /recuperacao');
        }
        
        $v = Validator::make($_POST, [
            'nome' => 'required',
            'data_inicio' => 'required',
            'data_fim' => 'required'
        ]);

        if ($v->fails()) {
            Flash::error('Preencha os campos obrigatórios corretamente.');
            return header('Location: /recuperacao');
        }

        $dados = $v->sanitized();
        $tenantId = currentUser()['tenant_id'];
        $anoAtivo = (new AnoLetivo())->ativo($tenantId);

        $periodoModel = new PeriodoRecuperacao();
        $bd = \App\Helpers\Database::getInstance();
        
        $sql = "INSERT INTO periodos_recuperacao (tenant_id, ano_letivo_id, periodo_id, nome, data_inicio, data_fim, aberto_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $bd->prepare($sql);
        $stmt->execute([
            $tenantId,
            $anoAtivo['id'],
            !empty($dados['periodo_id']) ? $dados['periodo_id'] : null,
            $dados['nome'],
            $dados['data_inicio'],
            $dados['data_fim'],
            currentUser()['id']
        ]);

        Flash::success('Período de recuperação criado!');
        return header('Location: /recuperacao');
    }

    /**
     * Tela de Lancar Notas da Recuperação para o professor.
     */
    public function lancar(int $periodoRecId, int $turmaId, int $disciplinaId)
    {
        $tenantId = currentUser()['tenant_id'];
        $db = \App\Helpers\Database::getInstance();
        
        // Pega os dados do periodo_recuperacao
        $stmt = $db->prepare("SELECT * FROM periodos_recuperacao WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$periodoRecId, $tenantId]);
        $periodoRec = $stmt->fetch();

        if (!$periodoRec) {
            Flash::error('Período de recuperação inválido.');
            return header('Location: /recuperacao');
        }

        // Pega Turma, Disciplina e Alunos Ativos
        $turma = (new Turma())->findById($tenantId, $turmaId);
        $disciplina = (new Disciplina())->findById($tenantId, $disciplinaId);
        $alunos = (new Aluno())->porTurma($tenantId, $turmaId);
        
        // Pega as Notas de Base já existentes para base da regra de superação
        $avaliacoes = (new \App\Models\Avaliacao())->porPeriodo($tenantId, $turmaId, $disciplinaId, $periodoRec['periodo_id'] ?? 0);
        $notasLancadasBase = (new \App\Models\Nota())->porPeriodoTurma($tenantId, $periodoRec['periodo_id'] ?? 0, $disciplinaId, $turmaId);
        
        $notasJaAferidasRec = (new NotaRecuperacao())->obterLancamentos($tenantId, $periodoRecId, $turmaId, $disciplinaId);

        $hoje = date('Y-m-d');
        $bloqueado = !($hoje >= $periodoRec['data_inicio'] && $hoje <= $periodoRec['data_fim'] && $periodoRec['ativo']);

        return View::render('recuperacao/lancar', [
            'periodoRec' => $periodoRec,
            'turma' => $turma,
            'disciplina' => $disciplina,
            'alunos' => $alunos,
            'avaliacoes' => $avaliacoes,
            'notasLancadasBase' => $notasLancadasBase,
            'notasJaAferidasRec' => $notasJaAferidasRec,
            'bloqueado' => $bloqueado,
            'pageTitle' => 'Lançar Notas - Recuperação'
        ]);
    }

    /**
     * Ação de salvar notas inseridas de Recuperação em POST
     */
    public function salvarNotas()
    {
        Csrf::verify();
        
        $tenantId = currentUser()['tenant_id'];
        $periodoRecId = (int)$_POST['periodo_rec_id'];
        $turmaId = (int)$_POST['turma_id'];
        $disciplinaId = (int)$_POST['disciplina_id'];
        $notasBrutas = $_POST['notas_rec'] ?? [];

        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare("SELECT periodo_id, data_inicio, data_fim, ativo FROM periodos_recuperacao WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$periodoRecId, $tenantId]);
        $pr = $stmt->fetch();

        $hoje = date('Y-m-d');
        if (!$pr || !($hoje >= $pr['data_inicio'] && $hoje <= $pr['data_fim'] && $pr['ativo'])) {
            Flash::error('Período de recuperação bloqueado ou inexistente!');
            return header("Location: /notas");
        }

        try {
            $service = new RecuperacaoService();
            $service->salvarNotasLote(
                $tenantId, 
                $periodoRecId, 
                $turmaId, 
                $disciplinaId, 
                $pr['periodo_id'] ?? 0, 
                $notasBrutas, 
                currentUser()['id']
            );
            
            Flash::success('Notas de Recuperação salvas com sucesso!');
        } catch (\Exception $e) {
            Flash::error('Erro ao salvar as notas de recuperação.');
        }

        return header("Location: /recuperacao/lancar/{$periodoRecId}/{$turmaId}/{$disciplinaId}");
    }
}
