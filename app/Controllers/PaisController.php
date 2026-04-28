<?php

namespace App\Controllers;

use App\Helpers\View;
use App\Helpers\Flash;
use App\Models\Responsavel;
use App\Models\Aluno;

class PaisController
{
    public function dashboard(): void
    {
        $paiId = (int) ($_SESSION['pai_id'] ?? 0);
        $tenantId = tenantId();

        if (!$paiId) {
            redirect('/pais/login');
        }

        $respModel = new Responsavel();
        $resp = $respModel->withEndereco($paiId, $tenantId);
        if (!$resp) {
            Flash::error('Responsável não encontrado.');
            redirect('/pais/login');
        }

        $alunoModel = new Aluno();
        $aluno = $alunoModel->findById((int) $resp['aluno_id'], $tenantId);

        View::render('pais/dashboard', [
            'pageTitle' => 'Portal dos Pais',
            'responsavel' => $resp,
            'aluno' => $aluno,
        ], 'pais');
    }

    public function boletim(): void
    {
        View::render('pais/boletim', ['pageTitle' => 'Boletim'], 'pais');
    }

    public function frequencia(): void
    {
        View::render('pais/frequencia', ['pageTitle' => 'Frequência'], 'pais');
    }

    public function agenda(): void
    {
        View::render('pais/agenda', ['pageTitle' => 'Agenda'], 'pais');
    }

    public function comunicados(): void
    {
        $paiId = (int) ($_SESSION['pai_id'] ?? 0);
        $tenantId = tenantId();
        if (!$paiId) redirect('/pais/login');

        $comModel = new \App\Models\Comunicado();
        $comunicados = $comModel->forResponsavel($paiId, $tenantId);

        View::render('pais/comunicados', ['pageTitle' => 'Comunicados', 'comunicados' => $comunicados], 'pais');
    }

    public function confirmarLeitura(array $params): void
    {
        $id = (int) $params['id'];
        $paiId = (int) ($_SESSION['pai_id'] ?? 0);
        if (!$paiId) redirect('/pais/login');

        $db = \App\Helpers\Database::getInstance();
        // Usa responsavel_id conforme migration
        $stmt = $db->prepare('INSERT INTO comunicados_leitura (comunicado_id, responsavel_id, lido_em) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lido_em = ?');
        $now = date('Y-m-d H:i:s');
        $stmt->execute([$id, $paiId, $now, $now]);

        Flash::success('Confirmação registrada. Obrigado.');
        redirect('/pais/comunicados');
    }

    public function diario(): void
    {
        View::render('pais/diario', ['pageTitle' => 'Diário'], 'pais');
    }
}
