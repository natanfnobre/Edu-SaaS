<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf, Validator, Upload};
use App\Models\{Tenant, User, AnoLetivo, Disciplina, LogAuditoria};

class TenantController
{
    private Tenant $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new Tenant();
    }

    // ── Configurações ─────────────────────────────────────────────

    public function index(): void
    {
        $tenantId = tenantId();
        $tenant   = $this->tenantModel->findById($tenantId);
        $visual   = $this->tenantModel->getVisual($tenantId);
        $academico = $this->tenantModel->getAcademico($tenantId);

        // Anos letivos
        $anoModel    = new AnoLetivo();
        $anosLetivos = $anoModel->all($tenantId, 'data_inicio DESC');

        // Disciplinas
        $discModel   = new Disciplina();
        $disciplinas = $discModel->all($tenantId, 'nome ASC');

        View::render('config/index', [
            'pageTitle'   => 'Configurações',
            'tenant'      => $tenant,
            'visual'      => $visual,
            'academico'   => $academico,
            'anosLetivos' => $anosLetivos,
            'disciplinas' => $disciplinas,
        ]);
    }

    // ── Visual ────────────────────────────────────────────────────

    public function updateVisual(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/configuracoes');
        }

        $tenantId = tenantId();
        $dados = [];

        // Logo (upload)
        if (!empty($_FILES['logo']['name'])) {
            $upload = new Upload();
            $logo   = $upload->image($_FILES['logo'], 'logos');
            if ($logo) $dados['logo_path'] = $logo;
        }

        // Cores
        if (!empty($_POST['cor_primaria'])) {
            $dados['cor_primaria'] = $_POST['cor_primaria'];
        }
        if (!empty($_POST['cor_secundaria'])) {
            $dados['cor_secundaria'] = $_POST['cor_secundaria'];
        }
        if (!empty($_POST['cor_texto'])) {
            $dados['cor_texto'] = $_POST['cor_texto'];
        }
        if (!empty($_POST['tema_padrao'])) {
            $dados['tema_padrao'] = $_POST['tema_padrao'];
        }

        $this->tenantModel->updateVisual($tenantId, $dados);

        // Log
        $logModel = new LogAuditoria();
        $logModel->registrar('tenant.visual_atualizado', 'tenant', $tenantId, null, $dados);

        Flash::success('Configuração visual atualizada! Recarregue a página para ver as mudanças.');
        redirect('/configuracoes');
    }

    // ── Acadêmico ─────────────────────────────────────────────────

    public function updateAcademico(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/configuracoes');
        }

        $v = Validator::make($_POST, [
            'tipo_periodo'               => 'required|in:bimestre,trimestre,semestre',
            'qtd_periodos'               => 'required',
            'qtd_avaliacoes_por_periodo' => 'required',
            'formula_media'              => 'required|in:simples,ponderada,custom',
            'nota_minima_aprovacao'      => 'required',
            'percentual_maximo_faltas'   => 'required',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/configuracoes');
        }

        $dados = $v->sanitized();
        $dados['plano_aula_habilitado']  = isset($_POST['plano_aula_habilitado']) ? 1 : 0;
        $dados['recuperacao_automatica'] = isset($_POST['recuperacao_automatica']) ? 1 : 0;

        $this->tenantModel->updateAcademico(tenantId(), $dados);

        Flash::success('Configuração acadêmica atualizada!');
        redirect('/configuracoes');
    }

    // ── Anos Letivos ──────────────────────────────────────────────

    public function storeAnoLetivo(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/configuracoes');
        }

        $v = Validator::make($_POST, [
            'nome'        => 'required|min:4|max:50',
            'data_inicio' => 'required|date',
            'data_fim'    => 'required|date',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/configuracoes');
        }

        $dados = $v->sanitized();
        $dados['tenant_id'] = tenantId();
        $dados['ativo']     = 0;

        $anoModel = new AnoLetivo();
        $id = $anoModel->create($dados);

        // Gera períodos automático
        $anoModel->criarPeriodos($id, tenantId());

        Flash::success("Ano letivo '{$dados['nome']}' criado com sucesso! Os períodos foram gerados automaticamente.");
        redirect('/configuracoes');
    }

    public function ativarAnoLetivo(array $params): void
    {
        $id = (int) $params['id'];
        $anoModel = new AnoLetivo();
        $anoModel->ativar($id, tenantId());

        Flash::success('Ano letivo ativado com sucesso!');
        redirect('/configuracoes');
    }

    // ── Disciplinas ───────────────────────────────────────────────

    public function storeDisciplina(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/configuracoes');
        }

        $v = Validator::make($_POST, [
            'nome'                   => 'required|min:2|max:100',
            'carga_horaria_semanal' => 'required|integer',
            'cor_icone'              => 'required',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/configuracoes');
        }

        $dados = $v->sanitized();
        $dados['tenant_id'] = tenantId();

        $discModel = new Disciplina();
        $discModel->create($dados);

        Flash::success('Disciplina criada com sucesso!');
        redirect('/configuracoes');
    }

    public function updateDisciplina(array $params): void
    {
        $id = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/configuracoes');
        }

        $v = Validator::make($_POST, [
            'nome'                   => 'required|min:2|max:100',
            'carga_horaria_semanal' => 'required|integer',
            'cor_icone'              => 'required',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/configuracoes');
        }

        $discModel = new Disciplina();
        $discModel->update($id, $v->sanitized(), tenantId());

        Flash::success('Disciplina atualizada!');
        redirect('/configuracoes');
    }

    public function destroyDisciplina(array $params): void
    {
        $id = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/configuracoes');
        }

        $discModel = new Disciplina();
        
        // Verifica se há vínculos (simplificado)
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM professor_disciplina_turma WHERE disciplina_id = ? AND tenant_id = ?');
        $stmt->execute([$id, tenantId()]);
        if ((int) $stmt->fetchColumn() > 0) {
            Flash::error('Não é possível remover uma disciplina que possui professores ou turmas vinculados.');
            redirect('/configuracoes');
        }

        $discModel->delete($id, tenantId());

        Flash::success('Disciplina removida.');
        redirect('/configuracoes');
    }

    // ── Usuários ──────────────────────────────────────────────────

    public function usuarios(): void
    {
        $userModel = new User();
        $usuarios  = $userModel->all(tenantId(), 'nome ASC');

        View::render('config/usuarios', [
            'pageTitle' => 'Usuários',
            'usuarios'  => $usuarios,
        ]);
    }

    public function storeUsuario(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/usuarios');
        }

        $v = Validator::make($_POST, [
            'nome'   => 'required|min:3|max:150',
            'email'  => 'required|email',
            'papel'  => 'required|in:diretor,coordenador,secretaria,professor',
            'senha'  => 'required|min:8',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/usuarios');
        }

        $dados = $v->sanitized();
        $dados['tenant_id'] = tenantId();
        $dados['cpf'] = !empty($_POST['cpf']) ? preg_replace('/[^0-9]/', '', $_POST['cpf']) : null;

        $userModel = new User();

        // Verifica se email já existe
        if ($userModel->findByEmail($dados['email'], tenantId())) {
            Flash::error('Este e-mail já está cadastrado.');
            redirect('/usuarios');
        }

        $userModel->create($dados);

        Flash::success('Usuário criado com sucesso!');
        redirect('/usuarios');
    }

    public function updateUsuario(array $params): void
    {
        $userId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/usuarios');
        }

        $v = Validator::make($_POST, [
            'nome'  => 'required|min:3|max:150',
            'email' => 'required|email',
            'papel' => 'required|in:diretor,coordenador,secretaria,professor',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/usuarios');
        }

        $dados = $v->sanitized();

        // Senha (opcional)
        if (!empty($_POST['senha'])) {
            $dados['senha'] = $_POST['senha'];
        }

        $userModel = new User();
        $userModel->update($userId, $dados, tenantId());

        Flash::success('Usuário atualizado!');
        redirect('/usuarios');
    }

    public function destroyUsuario(array $params): void
    {
        $userId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/usuarios');
        }

        // Não pode deletar a si mesmo
        if ($userId === (int) $_SESSION['user_id']) {
            Flash::error('Você não pode remover seu próprio usuário.');
            redirect('/usuarios');
        }

        $userModel = new User();
        $userModel->softDelete($userId, tenantId());

        Flash::success('Usuário removido.');
        redirect('/usuarios');
    }

    // ── Auditoria ─────────────────────────────────────────────────

    public function auditoria(): void
    {
        $logModel = new LogAuditoria();
        $logs     = $logModel->recentes(tenantId(), 100);

        View::render('config/auditoria', [
            'pageTitle' => 'Auditoria',
            'logs'      => $logs,
        ]);
    }
}
