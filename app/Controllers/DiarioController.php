<?php

namespace App\Controllers;

use App\Helpers\{Flash, Validator, Csrf};
use App\Models\DiarioAnotacao;

class DiarioController
{
    private DiarioAnotacao $diarioModel;

    public function __construct()
    {
        $this->diarioModel = new DiarioAnotacao();
    }

    /**
     * Endpoint para salvar uma anotação sobre um aluno (Acessado por Professores e Admins CoordX)
     */
    public function store(array $params): void
    {
        $alunoId = (int) $params['aluno_id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida ou expirada.');
            redirect('/alunos/' . $alunoId);
        }

        $v = Validator::make($_POST, [
            'titulo'       => 'required|min:3|max:150',
            'categoria'    => 'required|in:comportamento,aprendizado,elogio,saude,familiar,outro',
            'visibilidade' => 'required|in:somente_autor,professores,coordenacao,pais,todos',
            'conteudo'     => 'required|min:5'
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/alunos/' . $alunoId);
        }

        $dados = $v->sanitized();
        $dados['tenant_id'] = tenantId();
        $dados['aluno_id']  = $alunoId;
        $dados['autor_id']  = currentUser()['id'];
        
        $this->diarioModel->create($dados);

        Flash::success('Anotação registrada com sucesso no diário.');
        redirect('/alunos/' . $alunoId);
    }

    /**
     * Deleta a anotação se pertencer ao autor ou aos coordenadores superiores
     */
    public function destroy(array $params): void
    {
        $anotacaoId = (int) $params['anotacao_id'];
        $alunoId = (int) $params['id'];

        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/alunos/' . $alunoId);
        }

        $anotacao = $this->diarioModel->findById($anotacaoId, tenantId());
        
        if (!$anotacao) {
            Flash::error('Anotação não existe.');
            redirect('/alunos/' . $alunoId);
        }

        // Restrito apenas caso você não seja o autor e for professor sem adm
        $role = strtolower(currentUser()['papel']);
        if ($anotacao['autor_id'] != currentUser()['id'] && in_array($role, ['professor', 'responsavel'])) {
            Flash::error('Você só pode apagar suas próprias anotações.');
            redirect('/alunos/' . $alunoId);
        }

        $this->diarioModel->delete($anotacaoId, tenantId());

        Flash::success('Anotação removida do diário.');
        redirect('/alunos/' . $alunoId);
    }
}
