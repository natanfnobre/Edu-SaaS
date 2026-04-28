<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf};
use App\Models\{Aula, Frequencia, Turma, Disciplina, LogAuditoria};
use App\Models\AnoLetivo;
use App\Services\FrequenciaService;

class FrequenciaController
{
    // ── Index: lista turmas com total de aulas e % presença ────────

    public function index(): void
    {
        $userId   = (int) currentUser()['id'];
        $tenantId = tenantId();
        $role     = auth()->role();

        $db = \App\Helpers\Database::getInstance();

        $anoAtivo = (new AnoLetivo())->findOneBy(['ativo' => 1], $tenantId);
        if (!$anoAtivo) {
            Flash::error('Nenhum ano letivo ativo.');
            redirect('/configuracoes');
        }

        if (in_array($role, ['diretor', 'coordenador', 'secretaria', 'super_admin'])) {
            $stmt = $db->prepare(
                'SELECT DISTINCT pdt.turma_id, pdt.disciplina_id,
                        t.nome as turma_nome, t.serie, t.turno,
                        d.nome as disciplina_nome, d.cor_icone
                 FROM professor_disciplina_turma pdt
                 INNER JOIN turmas      t ON t.id = pdt.turma_id
                 INNER JOIN disciplinas d ON d.id = pdt.disciplina_id
                 WHERE pdt.tenant_id = ? AND t.ano_letivo_id = ? AND t.ativo = 1
                 ORDER BY t.nome ASC, d.nome ASC'
            );
            $stmt->execute([$tenantId, $anoAtivo['id']]);
        } else {
            $stmt = $db->prepare(
                'SELECT DISTINCT pdt.turma_id, pdt.disciplina_id,
                        t.nome as turma_nome, t.serie, t.turno,
                        d.nome as disciplina_nome, d.cor_icone
                 FROM professor_disciplina_turma pdt
                 INNER JOIN turmas      t ON t.id = pdt.turma_id
                 INNER JOIN disciplinas d ON d.id = pdt.disciplina_id
                 WHERE pdt.user_id = ? AND pdt.tenant_id = ? AND t.ano_letivo_id = ? AND t.ativo = 1
                 ORDER BY t.nome ASC, d.nome ASC'
            );
            $stmt->execute([$userId, $tenantId, $anoAtivo['id']]);
        }

        $vinculos = $stmt->fetchAll();

        // Para cada vínculo, conta total de aulas registradas
        $aulaModel = new Aula();
        foreach ($vinculos as &$v) {
            $v['total_aulas'] = $aulaModel->totalAulasPorTurmaEDisciplina(
                $v['turma_id'], $v['disciplina_id'], $tenantId
            );
        }
        unset($v);

        View::render('frequencia/index', [
            'pageTitle' => 'Frequência',
            'vinculos'  => $vinculos,
            'anoAtivo'  => $anoAtivo,
        ]);
    }

    // ── Lancar: grade com toggles presente/falta por aula ──────────

    public function lancar(array $params): void
    {
        $turmaId      = (int) $params['turma_id'];
        $disciplinaId = (int) $params['disciplina_id'];
        $tenantId     = tenantId();

        $db = \App\Helpers\Database::getInstance();

        $turma = (new Turma())->findById($turmaId, $tenantId);
        if (!$turma) { Flash::error('Turma não encontrada.'); redirect('/frequencia'); }

        $disciplina = (new Disciplina())->findById($disciplinaId, $tenantId);
        if (!$disciplina) { Flash::error('Disciplina não encontrada.'); redirect('/frequencia'); }

        // Alunos matriculados
        $alunosStmt = $db->prepare(
            'SELECT a.id, a.nome_completo, a.foto_path
             FROM alunos a
             INNER JOIN matriculas m ON m.aluno_id = a.id
             WHERE m.turma_id = ? AND m.tenant_id = ? AND m.status = "ativo" AND a.ativo = 1
             ORDER BY a.nome_completo ASC'
        );
        $alunosStmt->execute([$turmaId, $tenantId]);
        $alunos = $alunosStmt->fetchAll();

        if (empty($alunos)) {
            Flash::error('Esta turma não possui alunos matriculados.');
            redirect('/frequencia');
        }

        // Frequência de cada aluno (resumo)
        $freqService = new FrequenciaService();
        $aulaModel   = new Aula();
        $freqModel   = new Frequencia();

        foreach ($alunos as &$al) {
            $resumo = $freqModel->resumoPorAluno($al['id'], $turmaId, $disciplinaId, $tenantId);
            $al['total_faltas']      = $resumo['total_faltas'];
            $al['total_aulas']       = $resumo['total_aulas'];
            $al['percentual_faltas'] = $resumo['percentual_faltas'];
        }
        unset($al);

        // Histórico recente de aulas desta turma/disciplina
        $aulas = $aulaModel->porTurmaEDisciplina($turmaId, $disciplinaId, $tenantId);

        View::render('frequencia/lancar', [
            'pageTitle'   => "Frequência — {$turma['nome']} · {$disciplina['nome']}",
            'turma'       => $turma,
            'disciplina'  => $disciplina,
            'alunos'      => $alunos,
            'aulas'       => $aulas,
            'hojeStr'     => date('Y-m-d'),
        ]);
    }

    // ── Salvar: registra a aula e a chamada em lote ────────────────

    public function salvar(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/frequencia');
        }

        $turmaId      = (int) ($_POST['turma_id']      ?? 0);
        $disciplinaId = (int) ($_POST['disciplina_id'] ?? 0);
        $data         = $_POST['data']         ?? date('Y-m-d');
        $numeroAulas  = (int) ($_POST['numero_aulas'] ?? 1);
        $conteudo     = trim($_POST['conteudo_dado'] ?? '');
        $userId       = (int) currentUser()['id'];
        $tenantId     = tenantId();

        // Valida data
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            Flash::error('Data inválida.');
            redirect("/frequencia/lancar/{$turmaId}/{$disciplinaId}");
        }

        // Descobre o período pela data
        $aulaModel = new Aula();
        $periodo   = $aulaModel->periodoAtivoPorData($turmaId, $data, $tenantId);

        if (!$periodo) {
            Flash::error('A data informada não está dentro de nenhum período letivo cadastrado.');
            redirect("/frequencia/lancar/{$turmaId}/{$disciplinaId}");
        }

        // Verifica se já existe aula registrada nesta data
        if ($aulaModel->existeNaData($turmaId, $disciplinaId, $data, $tenantId)) {
            Flash::error("Já existe uma aula registrada para esta turma/disciplina em " . date('d/m/Y', strtotime($data)) . ".");
            redirect("/frequencia/lancar/{$turmaId}/{$disciplinaId}");
        }

        // Cria o registro de aula
        $aulaId = $aulaModel->create([
            'tenant_id'    => $tenantId,
            'disciplina_id'=> $disciplinaId,
            'turma_id'     => $turmaId,
            'periodo_id'   => $periodo['id'],
            'data'         => $data,
            'numero_aulas' => max(1, $numeroAulas),
            'conteudo_dado'=> $conteudo ?: null,
            'criado_por'   => $userId,
        ]);

        // Monta frequências: presentes[] contém os IDs dos alunos presentes
        $presentesIds = array_map('intval', $_POST['presentes'] ?? []);
        $todosAlunosIds = array_map('intval', explode(',', $_POST['todos_alunos'] ?? ''));

        $frequencias = [];
        foreach ($todosAlunosIds as $alunoId) {
            if ($alunoId <= 0) continue;
            $frequencias[$alunoId] = in_array($alunoId, $presentesIds);
        }

        (new Frequencia())->salvarLote($aulaId, $frequencias, $tenantId);

        // Log
        (new LogAuditoria())->registrar(
            'frequencia.aula_registrada',
            'aula',
            $aulaId,
            null,
            ['turma_id' => $turmaId, 'disciplina_id' => $disciplinaId, 'data' => $data, 'faltas' => count(array_filter($frequencias, fn($p) => !$p))]
        );

        Flash::success('Chamada registrada com sucesso!');
        redirect("/frequencia/lancar/{$turmaId}/{$disciplinaId}");
    }

    // ── Justificar falta individual ────────────────────────────────

    public function justificar(array $params): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/frequencia');
        }

        $frequenciaId = (int) $params['frequencia_id'];
        $tenantId     = tenantId();

        $freqModel = new Frequencia();
        $freqModel->update($frequenciaId, [
            'justificada' => 1,
            'observacao'  => trim($_POST['observacao'] ?? ''),
        ], $tenantId);

        Flash::success('Falta justificada.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/frequencia');
    }
}
