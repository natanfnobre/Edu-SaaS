<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf, Validator};
use App\Models\{Turma, Aluno, AnoLetivo, Disciplina, User};

class TurmaController
{
    private Turma $turmaModel;

    public function __construct()
    {
        $this->turmaModel = new Turma();
    }

    public function index(): void
    {
        $tenantId = tenantId();
        
        // Ano letivo ativo
        $anoLetivoModel = new AnoLetivo();
        $anoAtivo = $anoLetivoModel->ativo($tenantId);

        if (!$anoAtivo) {
            Flash::warning('Nenhum ano letivo ativo. Crie um ano letivo primeiro.');
            redirect('/configuracoes');
        }

        $turmas = $this->turmaModel->porAnoLetivo($anoAtivo['id'], $tenantId);

        View::render('turmas/index', [
            'pageTitle' => 'Turmas',
            'turmas'    => $turmas,
            'anoLetivo' => $anoAtivo,
        ]);
    }

    public function create(): void
    {
        $anoLetivoModel = new AnoLetivo();
        $anosLetivos = $anoLetivoModel->all(tenantId(), 'data_inicio DESC');

        View::render('turmas/form', [
            'pageTitle'   => 'Nova Turma',
            'turma'       => null,
            'anosLetivos' => $anosLetivos,
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/turmas');
        }

        $v = Validator::make($_POST, [
            'nome'          => 'required|min:2|max:50',
            'ano_letivo_id' => 'required|integer',
            'turno'         => 'required|in:manha,tarde,noite,integral',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/turmas/nova');
        }

        $dados = $v->sanitized();
        $dados['tenant_id'] = tenantId();

        $turmaId = $this->turmaModel->create($dados);

        Flash::success('Turma criada com sucesso!');
        redirect('/turmas/' . $turmaId);
    }

    public function show(array $params): void
    {
        $turmaId  = (int) $params['id'];
        $turma    = $this->turmaModel->withDetalhes($turmaId, tenantId());

        if (!$turma) {
            Flash::error('Turma não encontrada.');
            redirect('/turmas');
        }

        // Alunos da turma
        $alunos = $this->turmaModel->alunos($turmaId, tenantId());

        // Todos os alunos (para adicionar) — filtramos para exibir apenas os disponíveis no mesmo ano
        $alunoModel    = new Aluno();
        $todosAlunos   = $alunoModel->all(tenantId(), 'nome_completo ASC');

        // IDs de alunos já matriculados no mesmo ano letivo (status ativo)
        $anoId = $turma['ano_letivo']['id'] ?? null;
        $db = \App\Helpers\Database::getInstance();
        $idsMatriculados = [];
        if ($anoId) {
            $stmt = $db->prepare('SELECT aluno_id FROM matriculas WHERE ano_letivo_id = ? AND tenant_id = ? AND status = "ativo"');
            $stmt->execute([$anoId, tenantId()]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) $idsMatriculados[] = (int) $r['aluno_id'];
        }

        // Filtra alunos disponíveis (não matriculados no ano)
        $alunosDisponiveis = array_filter($todosAlunos, function($a) use ($idsMatriculados) {
            return !in_array((int)$a['id'], $idsMatriculados, true);
        });

        // Professores disponíveis
        $userModel     = new User();
        $professores   = $userModel->byRole('professor', tenantId());

        // Disciplinas disponíveis
        $discModel     = new Disciplina();
        $disciplinas   = $discModel->all(tenantId(), 'nome ASC');

        View::render('turmas/show', [
            'pageTitle'   => $turma['nome'],
            'turma'       => $turma,
            'alunos'      => $alunos,
            'todosAlunos' => $alunosDisponiveis,
            'professores' => $professores,
            'disciplinas' => $disciplinas,
        ]);
    }

    public function edit(array $params): void
    {
        $turmaId = (int) $params['id'];
        $turma   = $this->turmaModel->findById($turmaId, tenantId());

        if (!$turma) {
            Flash::error('Turma não encontrada.');
            redirect('/turmas');
        }

        $anoLetivoModel = new AnoLetivo();
        $anosLetivos = $anoLetivoModel->all(tenantId(), 'data_inicio DESC');

        View::render('turmas/form', [
            'pageTitle'   => 'Editar Turma',
            'turma'       => $turma,
            'anosLetivos' => $anosLetivos,
        ]);
    }

    public function update(array $params): void
    {
        $turmaId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/turmas/' . $turmaId . '/editar');
        }

        // Verifica ação
        $acao = $_POST['acao'] ?? 'atualizar';

        // Adicionar aluno
        if ($acao === 'adicionar_aluno') {
            if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
                Flash::error('Requisição inválida.');
                redirect('/turmas/' . $turmaId);
            }
            $alunoId = (int) ($_POST['aluno_id'] ?? 0);
            $turma   = $this->turmaModel->findById($turmaId, tenantId());

            $ok = $this->turmaModel->adicionarAluno($turmaId, $alunoId, $turma['ano_letivo_id'], tenantId());
            if ($ok) {
                Flash::success('Aluno adicionado à turma!');
            } else {
                Flash::error('Não foi possível adicionar: o aluno já possui matrícula ativa neste ano letivo.');
            }
            redirect('/turmas/' . $turmaId);
        }

        // Remover aluno
        if ($acao === 'remover_aluno') {
            $alunoId = (int) ($_POST['aluno_id'] ?? 0);
            $this->turmaModel->removerAluno($turmaId, $alunoId);
            Flash::success('Aluno removido da turma.');
            redirect('/turmas/' . $turmaId);
        }

        // Vincular disciplina/professor
        if ($acao === 'vincular_professor') {
            $profId = (int) ($_POST['professor_id'] ?? 0);
            $discId = (int) ($_POST['disciplina_id'] ?? 0);
            $turma  = $this->turmaModel->findById($turmaId, tenantId());

            $discModel = new Disciplina();
            $discModel->vincularProfessor($discId, $profId, $turmaId, $turma['ano_letivo_id'], tenantId());

            Flash::success('Professor vinculado à disciplina!');
            redirect('/turmas/' . $turmaId);
        }

        // Atualizar dados da turma
        $v = Validator::make($_POST, [
            'nome'  => 'required|min:2|max:50',
            'turno' => 'required|in:manha,tarde,noite,integral',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/turmas/' . $turmaId . '/editar');
        }

        $this->turmaModel->update($turmaId, $v->sanitized(), tenantId());

        Flash::success('Turma atualizada!');
        redirect('/turmas/' . $turmaId);
    }
}
