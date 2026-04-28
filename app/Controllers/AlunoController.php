<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf, Validator, Upload};
use App\Models\{Aluno, Responsavel, Turma, AnoLetivo};
use App\Services\AuthService;

class AlunoController
{
    private Aluno $alunoModel;
    private AuthService $auth;

    public function __construct()
    {
        $this->alunoModel = new Aluno();
        $this->auth = new AuthService();
    }

    // ── Listagem ──────────────────────────────────────────────────

    public function index(): void
    {
        $tenantId = tenantId();
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $busca    = $_GET['busca'] ?? '';

        if ($busca) {
            $alunos = $this->alunoModel->search($busca, $tenantId, 100);
            $paginacao = ['items' => $alunos, 'total' => count($alunos), 'per_page' => 100, 'current_page' => 1, 'last_page' => 1];
        } else {
            $paginacao = $this->alunoModel->paginate($page, 20, ['ativo' => 1], $tenantId, 'nome_completo ASC');
        }

        View::render('alunos/index', [
            'pageTitle'  => 'Alunos',
            'alunos'     => $paginacao['items'],
            'paginacao'  => $paginacao,
            'busca'      => $busca,
        ]);
    }

    // ── Criar ─────────────────────────────────────────────────────

    public function create(): void
    {
        View::render('alunos/form', [
            'pageTitle' => 'Cadastrar Aluno',
            'aluno'     => null,
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/alunos');
        }

        $v = Validator::make($_POST, [
            'nome_completo'   => 'required|min:3|max:200',
            'data_nascimento' => 'date',
            'cpf'             => 'cpf',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/alunos/novo');
        }

        $dados = $v->sanitized();
        // Remove campos do endereço do array principal de aluno para evitar colisão na query de Create do Model Aluno
        $camposEndereco = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'];
        foreach ($camposEndereco as $campo) unset($dados[$campo]);
        
        $dados['tenant_id'] = tenantId();
        $dados['cpf'] = !empty($dados['cpf']) ? preg_replace('/[^0-9]/', '', $dados['cpf']) : null;

        // Foto (opcional) com Limite de peso
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['foto']['size'] > 2097152) { // Max 2MB para não sobrecarregar BD e servidor
                Flash::error('A foto da matrícula deve ter no máximo 2MB.');
                redirect('/alunos/novo');
            }
            $upload = new Upload();
            $foto   = $upload->image($_FILES['foto'], 'alunos');
            if ($foto) $dados['foto_path'] = $foto;
        }

        // Endereço
        $endereco = [
            'logradouro'  => $_POST['logradouro'] ?? null,
            'numero'      => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro'      => $_POST['bairro'] ?? null,
            'cidade'      => $_POST['cidade'] ?? null,
            'estado'      => $_POST['estado'] ?? null,
            'cep'         => $_POST['cep'] ?? null,
        ];

        $alunoId = $this->alunoModel->createWithEndereco($dados, $endereco);

        Flash::success('Aluno cadastrado com sucesso!');
        redirect('/alunos/' . $alunoId);
    }

    // ── Visualizar ────────────────────────────────────────────────

    public function show(array $params): void
    {
        $alunoId  = (int) $params['id'];
        $tenantId = tenantId();

        $aluno = $this->alunoModel->withResponsaveis($alunoId, $tenantId);
        if (!$aluno) {
            Flash::error('Aluno não encontrado.');
            redirect('/alunos');
        }

        // Turmas do aluno
        $turmas = $this->alunoModel->turmasDoAluno($alunoId, $tenantId);

        // Carrega anos letivos e turmas disponíveis para matrícula (Fase6)
        $anoModel = new AnoLetivo();
        $anos = $anoModel->all($tenantId, 'data_inicio DESC');

        $turmaModel = new Turma();
        $turmasAll = $turmaModel->all($tenantId, 'nome ASC');

        // Diário de Anotações (Segregado por hierarquia de permissão do Model)
        $diarioModel = new \App\Models\DiarioAnotacao();
        $anotacoes = $diarioModel->getAnotacoesTimeline($alunoId, $tenantId, currentUser());

        View::render('alunos/show', [
            'pageTitle' => $aluno['nome_completo'],
            'aluno'     => $aluno,
            'turmas'    => $turmas,
            'anotacoes' => $anotacoes,
            'anos'      => $anos,
            'turmas_all'=> $turmasAll,
        ]);
    }

    // Matrícula rápida — cria registro em matriculas
    public function matricular(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/alunos');
        }

        if (!can('alunos.editar')) {
            Flash::error('Sem permissão para matricular.');
            redirect('/alunos');
        }

        $alunoId = (int) ($_POST['aluno_id'] ?? 0);
        $turmaId = (int) ($_POST['turma_id'] ?? 0);
        $anoId   = (int) ($_POST['ano_letivo_id'] ?? 0);
        $tenantId = tenantId();

        if (!$alunoId || !$turmaId || !$anoId) {
            Flash::error('Preencha ano e turma para matricular.');
            redirect('/alunos/' . $alunoId);
        }

        $turmaModel = new Turma();
        // Gera número de matrícula simples
        $numero = date('Y') . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $ok = $turmaModel->adicionarAluno($turmaId, $alunoId, $anoId, $tenantId, $numero);
        if ($ok) {
            Flash::success('Aluno matriculado com sucesso!');
        } else {
            Flash::error('Não foi possível matricular (pode já existir matrícula).');
        }

        redirect('/alunos/' . $alunoId);
    }

    public function transferir(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/alunos');
        }

        if (!can('alunos.editar')) {
            Flash::error('Sem permissão.');
            redirect('/alunos');
        }

        $alunoId = (int) ($_POST['aluno_id'] ?? 0);
        $turmaDestinoId = (int) ($_POST['turma_destino_id'] ?? 0);
        $tenantId = tenantId();

        if (!$alunoId || !$turmaDestinoId) {
            Flash::error('Dados incompletos para transferência.');
            redirect('/alunos/' . $alunoId);
        }

        // Busca turma destino para obter ano letivo
        $turmaModel = new Turma();
        $turmaDest = $turmaModel->findById($turmaDestinoId, $tenantId);
        if (!$turmaDest) {
            Flash::error('Turma destino não encontrada.');
            redirect('/alunos/' . $alunoId);
        }

        $anoId = $turmaDest['ano_letivo_id'];

        $db = \App\Helpers\Database::getInstance();
        // Verifica matrícula ativa do aluno no ano
        $stmt = $db->prepare('SELECT id, turma_id FROM matriculas WHERE aluno_id = ? AND ano_letivo_id = ? AND tenant_id = ? AND status = "ativo" LIMIT 1');
        $stmt->execute([$alunoId, $anoId, $tenantId]);
        $mat = $stmt->fetch();

        try {
            $db->beginTransaction();

            if ($mat) {
                if ((int)$mat['turma_id'] === $turmaDestinoId) {
                    $db->rollBack();
                    Flash::error('Aluno já está matriculado nesta turma.');
                    redirect('/alunos/' . $alunoId);
                }
                // Marca a matrícula atual como transferida
                $db->prepare('UPDATE matriculas SET status = ? WHERE id = ?')->execute(['transferido', $mat['id']]);
            }

            // Tenta adicionar na turma destino
            $ok = $turmaModel->adicionarAluno($turmaDestinoId, $alunoId, $anoId, $tenantId);
            if (!$ok) {
                $db->rollBack();
                Flash::error('Não foi possível matricular na turma destino.');
                redirect('/alunos/' . $alunoId);
            }

            $db->commit();
            Flash::success('Transferência realizada com sucesso.');
        } catch (\Throwable $e) {
            $db->rollBack();
            Flash::error('Erro ao transferir: ' . $e->getMessage());
        }

        redirect('/alunos/' . $alunoId);
    }

    public function cancelarMatricula(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/alunos');
        }

        if (!can('alunos.editar')) {
            Flash::error('Sem permissão.');
            redirect('/alunos');
        }

        $alunoId = (int) ($_POST['aluno_id'] ?? 0);
        $anoId   = (int) ($_POST['ano_letivo_id'] ?? 0);
        $tenantId = tenantId();

        if (!$alunoId || !$anoId) {
            Flash::error('Dados incompletos.');
            redirect('/alunos/' . $alunoId);
        }

        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare('UPDATE matriculas SET status = ? WHERE aluno_id = ? AND ano_letivo_id = ? AND tenant_id = ? AND status = "ativo"');
        $stmt->execute(['cancelado', $alunoId, $anoId, $tenantId]);

        Flash::success('Matrícula cancelada.');
        redirect('/alunos/' . $alunoId);
    }

    // ── Editar ────────────────────────────────────────────────────

    public function edit(array $params): void
    {
        $alunoId  = (int) $params['id'];
        $aluno    = $this->alunoModel->withResponsaveis($alunoId, tenantId());

        if (!$aluno) {
            Flash::error('Aluno não encontrado.');
            redirect('/alunos');
        }

        View::render('alunos/form', [
            'pageTitle' => 'Editar Aluno',
            'aluno'     => $aluno,
        ]);
    }

    public function update(array $params): void
    {
        $alunoId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/alunos/' . $alunoId . '/editar');
        }

        $v = Validator::make($_POST, [
            'nome_completo'   => 'required|min:3|max:200',
            'data_nascimento' => 'date',
            'cpf'             => 'cpf',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/alunos/' . $alunoId . '/editar');
        }

        $dados = $v->sanitized();
        $dados['cpf'] = !empty($dados['cpf']) ? preg_replace('/[^0-9]/', '', $dados['cpf']) : null;

        // Foto e Limite
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['foto']['size'] > 2097152) { // 2MB limite
                Flash::error('A foto de perfil excede o limite de 2MB. Envie uma menor para não sobrecarregar nossa base.');
                redirect('/alunos/' . $alunoId . '/editar');
            }

            $upload = new Upload();
            $foto   = $upload->image($_FILES['foto'], 'alunos');
            if ($foto) {
                // Delete a foto velha se existente do disco
                $alunoAntigo = $this->alunoModel->findById($alunoId, tenantId());
                if (!empty($alunoAntigo['foto_path'])) {
                    $oldPath = __DIR__ . '/../../public/assets/uploads/' . $alunoAntigo['foto_path'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $dados['foto_path'] = $foto;
            }
        }

        // Endereço
        $endereco = [
            'logradouro'  => $_POST['logradouro'] ?? null,
            'numero'      => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro'      => $_POST['bairro'] ?? null,
            'cidade'      => $_POST['cidade'] ?? null,
            'estado'      => $_POST['estado'] ?? null,
            'cep'         => $_POST['cep'] ?? null,
        ];

        $this->alunoModel->updateWithEndereco($alunoId, $dados, $endereco, tenantId());

        Flash::success('Aluno atualizado com sucesso!');
        redirect('/alunos/' . $alunoId);
    }

    // ── Deletar ───────────────────────────────────────────────────

    public function destroy(array $params): void
    {
        $alunoId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/alunos');
        }

        // Soft delete
        $this->alunoModel->softDelete($alunoId, tenantId());

        Flash::success('Aluno removido.');
        redirect('/alunos');
    }

    // ── Responsáveis ──────────────────────────────────────────────

    public function createResponsavel(array $params): void
    {
        $alunoId = (int) $params['id'];
        $aluno   = $this->alunoModel->findById($alunoId, tenantId());

        if (!$aluno) {
            Flash::error('Aluno não encontrado.');
            redirect('/alunos');
        }

        View::render('alunos/responsavel-form', [
            'pageTitle'    => 'Adicionar Responsável',
            'aluno'        => $aluno,
            'responsavel'  => null,
        ]);
    }

    public function storeResponsavel(array $params): void
    {
        $alunoId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/alunos/' . $alunoId);
        }

        $v = Validator::make($_POST, [
            'nome_completo' => 'required|min:3|max:200',
            'parentesco'    => 'required',
            'cpf'           => 'cpf',
            'telefone'      => 'required',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/alunos/' . $alunoId . '/responsaveis/novo');
        }

        $dados = $v->sanitized();
        $dados['tenant_id'] = tenantId();
        $dados['aluno_id']  = $alunoId;
        $dados['cpf'] = !empty($dados['cpf']) ? preg_replace('/[^0-9]/', '', $dados['cpf']) : null;
        $dados['contato_emergencia'] = isset($_POST['contato_emergencia']) ? 1 : 0;
        $dados['pode_buscar_aluno']  = isset($_POST['pode_buscar_aluno']) ? 1 : 0;

        $respModel = new Responsavel();
        $respModel->createWithSenha($dados);

        Flash::success('Responsável adicionado com sucesso! A senha padrão foi gerada automaticamente.');
        redirect('/alunos/' . $alunoId);
    }

    public function updateResponsavel(array $params): void
    {
        $alunoId = (int) $params['id'];
        $respId  = (int) $params['rid'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/alunos/' . $alunoId);
        }

        $v = Validator::make($_POST, [
            'nome_completo' => 'required|min:3|max:200',
            'parentesco'    => 'required',
            'telefone'      => 'required',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/alunos/' . $alunoId);
        }

        $dados = $v->sanitized();
        $dados['contato_emergencia'] = isset($_POST['contato_emergencia']) ? 1 : 0;
        $dados['pode_buscar_aluno']  = isset($_POST['pode_buscar_aluno']) ? 1 : 0;

        $respModel = new Responsavel();
        $respModel->update($respId, $dados, tenantId());

        Flash::success('Responsável atualizado!');
        redirect('/alunos/' . $alunoId);
    }
}
