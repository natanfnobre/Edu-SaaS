<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf, Validator};
use App\Models\{Avaliacao, Nota, Turma, Disciplina, Aluno, LogAuditoria};
use App\Models\AnoLetivo;
use App\Services\MediaService;

class NotaController
{
    // ── Index: lista turmas do professor com status de lançamento ──

    public function index(): void
    {
        $userId   = (int) currentUser()['id'];
        $tenantId = tenantId();
        $role     = auth()->role();

        $db = \App\Helpers\Database::getInstance();

        // Busca o ano letivo ativo
        $anoAtivo = (new AnoLetivo())->findOneBy(['ativo' => 1], $tenantId);

        if (!$anoAtivo) {
            Flash::error('Nenhum ano letivo ativo. Acesse Configurações para ativar um.');
            redirect('/configuracoes');
        }

        // Períodos do ano ativo
        $periodos = $db->prepare(
            'SELECT * FROM periodos WHERE ano_letivo_id = ? AND tenant_id = ? ORDER BY numero ASC'
        );
        $periodos->execute([$anoAtivo['id'], $tenantId]);
        $periodos = $periodos->fetchAll();

        // Turmas/disciplinas do professor (ou todas para coordenação/diretor)
        if (in_array($role, ['diretor', 'coordenador', 'secretaria', 'super_admin'])) {
            $stmt = $db->prepare(
                'SELECT DISTINCT pdt.turma_id, pdt.disciplina_id, p.id as periodo_id,
                        t.nome as turma_nome, t.serie, t.turno,
                        d.nome as disciplina_nome, d.cor_icone,
                        p.nome as periodo_nome, p.numero as periodo_numero,
                        p.notas_bloqueadas
                 FROM professor_disciplina_turma pdt
                 INNER JOIN turmas      t ON t.id = pdt.turma_id
                 INNER JOIN disciplinas d ON d.id = pdt.disciplina_id
                 INNER JOIN periodos    p ON p.ano_letivo_id = t.ano_letivo_id
                 WHERE pdt.tenant_id = ? AND t.ano_letivo_id = ? AND t.ativo = 1
                 ORDER BY p.numero ASC, t.nome ASC, d.nome ASC'
            );
            $stmt->execute([$tenantId, $anoAtivo['id']]);
        } else {
            $stmt = $db->prepare(
                'SELECT DISTINCT pdt.turma_id, pdt.disciplina_id,
                        t.nome as turma_nome, t.serie, t.turno,
                        d.nome as disciplina_nome, d.cor_icone,
                        p.id as periodo_id, p.nome as periodo_nome, p.numero as periodo_numero,
                        p.notas_bloqueadas
                 FROM professor_disciplina_turma pdt
                 INNER JOIN turmas      t ON t.id = pdt.turma_id
                 INNER JOIN disciplinas d ON d.id = pdt.disciplina_id
                 INNER JOIN periodos    p ON p.ano_letivo_id = t.ano_letivo_id
                 WHERE pdt.user_id = ? AND pdt.tenant_id = ? AND t.ano_letivo_id = ? AND t.ativo = 1
                 ORDER BY p.numero ASC, t.nome ASC, d.nome ASC'
            );
            $stmt->execute([$userId, $tenantId, $anoAtivo['id']]);
        }

        $vinculos = $stmt->fetchAll();

        // Para cada vínculo, verifica se há notas lançadas
        $notaModel = new Nota();
        foreach ($vinculos as &$v) {
            $notas = $notaModel->porTurmaEPeriodo($v['turma_id'], $v['periodo_id'], $tenantId);
            $v['tem_notas'] = !empty($notas);
        }
        unset($v);

        View::render('notas/index', [
            'pageTitle' => 'Notas',
            'vinculos'  => $vinculos,
            'periodos'  => $periodos,
            'anoAtivo'  => $anoAtivo,
        ]);
    }

    // ── Lançar: grade de alunos com inputs inline ──────────────────

    public function lancar(array $params): void
    {
        $turmaId      = (int) $params['turma_id'];
        $disciplinaId = (int) $params['disciplina_id'];
        $periodoId    = (int) $params['periodo_id'];
        $userId       = (int) currentUser()['id'];
        $tenantId     = tenantId();

        $db = \App\Helpers\Database::getInstance();

        // Carrega dados contextuais
        $turma = (new Turma())->findById($turmaId, $tenantId);
        if (!$turma) { Flash::error('Turma não encontrada.'); redirect('/notas'); }

        $periodo = $db->prepare('SELECT * FROM periodos WHERE id = ? AND tenant_id = ?');
        $periodo->execute([$periodoId, $tenantId]);
        $periodo = $periodo->fetch();
        if (!$periodo) { Flash::error('Período não encontrado.'); redirect('/notas'); }

        $disciplina = (new Disciplina())->findById($disciplinaId, $tenantId);
        if (!$disciplina) { Flash::error('Disciplina não encontrada.'); redirect('/notas'); }

        // Alunos matriculados na turma
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
            redirect('/notas');
        }

        // Garante que existem avaliações (cria AV1/AV2 padrão se não houver)
        $avaliacaoModel = new Avaliacao();
        $avaliacaoModel->criarPadrao($turmaId, $disciplinaId, $periodoId, $tenantId, 2);
        $avaliacoes = $avaliacaoModel->porTurmaEPeriodo($turmaId, $periodoId, $disciplinaId, $tenantId);

        // Notas já lançadas: [aluno_id][avaliacao_id]
        $notasLancadas = (new Nota())->porTurmaEPeriodo($turmaId, $periodoId, $tenantId);

        View::render('notas/lancar', [
            'pageTitle'     => "Notas — {$turma['nome']} · {$disciplina['nome']}",
            'turma'         => $turma,
            'disciplina'    => $disciplina,
            'periodo'       => $periodo,
            'alunos'        => $alunos,
            'avaliacoes'    => $avaliacoes,
            'notasLancadas' => $notasLancadas,
            'bloqueado'     => (bool) ($periodo['notas_bloqueadas'] ?? false),
        ]);
    }

    // ── Salvar: recebe o POST com todas as notas em lote ──────────

    public function salvar(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/notas');
        }

        $turmaId      = (int) ($_POST['turma_id']      ?? 0);
        $disciplinaId = (int) ($_POST['disciplina_id'] ?? 0);
        $periodoId    = (int) ($_POST['periodo_id']    ?? 0);
        $userId       = (int) currentUser()['id'];
        $tenantId     = tenantId();

        // Verifica se o período não está bloqueado
        $db = \App\Helpers\Database::getInstance();
        $periodoStmt = $db->prepare('SELECT notas_bloqueadas FROM periodos WHERE id = ? AND tenant_id = ?');
        $periodoStmt->execute([$periodoId, $tenantId]);
        $periodo = $periodoStmt->fetch();

        if ($periodo && $periodo['notas_bloqueadas']) {
            Flash::error('Este período está bloqueado. Não é possível lançar notas.');
            redirect("/notas/lancar/{$turmaId}/{$disciplinaId}/{$periodoId}");
        }

        // Monta lote: notas[avaliacao_id][aluno_id] = valor
        $lote = [];
        $notasPost = $_POST['notas'] ?? [];

        foreach ($notasPost as $avaliacaoId => $alunos) {
            foreach ($alunos as $alunoId => $valor) {
                $lote[] = [
                    'avaliacao_id' => (int) $avaliacaoId,
                    'aluno_id'     => (int) $alunoId,
                    'nota'         => ($valor === '' || $valor === null) ? null : (float) $valor,
                    'obs'          => $_POST['obs'][$avaliacaoId][$alunoId] ?? null,
                ];
            }
        }

        (new Nota())->salvarLote($lote, $userId, $tenantId);

        // Log de auditoria
        (new LogAuditoria())->registrar(
            'nota.lancamento_lote',
            'turma',
            $turmaId,
            null,
            ['disciplina_id' => $disciplinaId, 'periodo_id' => $periodoId, 'total' => count($lote)]
        );

        Flash::success('Notas salvas com sucesso!');
        redirect("/notas/lancar/{$turmaId}/{$disciplinaId}/{$periodoId}");
    }

    // ── Boletim: visão consolidada de um aluno ────────────────────

    public function boletim(array $params): void
    {
        $alunoId  = (int) $params['aluno_id'];
        $tenantId = tenantId();

        $aluno = (new Aluno())->findById($alunoId, $tenantId);
        if (!$aluno) { Flash::error('Aluno não encontrado.'); redirect('/alunos'); }

        // Busca matricula ativa
        $db = \App\Helpers\Database::getInstance();
        $matriculaStmt = $db->prepare(
            'SELECT m.*, t.nome as turma_nome, t.id as turma_id
             FROM matriculas m
             INNER JOIN turmas t ON t.id = m.turma_id
             WHERE m.aluno_id = ? AND m.tenant_id = ? AND m.status = "ativo"
             LIMIT 1'
        );
        $matriculaStmt->execute([$alunoId, $tenantId]);
        $matricula = $matriculaStmt->fetch();

        $notasRaw = [];
        if ($matricula) {
            $notasRaw = (new Nota())->porAlunoETurma($alunoId, $matricula['turma_id'], $tenantId);
        }

        // Organiza por período → disciplina
        $boletim = [];
        $mediaService = new MediaService();

        foreach ($notasRaw as $n) {
            $p = $n['periodo_nome'];
            $d = $n['disciplina_nome'];
            $boletim[$p][$d]['cor_icone'] = $n['cor_icone'];
            $boletim[$p][$d]['notas'][]  = ['nota' => $n['nota'], 'peso' => $n['peso'], 'nome' => $n['avaliacao_nome']];
        }

        // Calcula médias
        $config = $db->prepare('SELECT formula_media, nota_minima_aprovacao FROM tenant_academico WHERE tenant_id = ? LIMIT 1');
        $config->execute([$tenantId]);
        $config = $config->fetch();

        foreach ($boletim as $periodo => &$disciplinas) {
            foreach ($disciplinas as $disc => &$dados) {
                $media = $mediaService->calcularMedia($dados['notas'], $config['formula_media'] ?? 'simples');
                $dados['media']  = $media;
                $dados['status'] = $mediaService->statusAluno($media, (float) ($config['nota_minima_aprovacao'] ?? 6.0));
            }
        }
        unset($disciplinas, $dados);

        // Incorpora Notas de Recuperação (Fase 4)
        $recDb = $db->prepare('
            SELECT nr.nota, nr.nota_substituiu, d.nome as disc_nome, p.nome as per_nome
            FROM notas_recuperacao nr
            JOIN disciplinas d ON nr.disciplina_id = d.id
            JOIN periodos_recuperacao pr ON pr.id = nr.periodo_recuperacao_id
            JOIN periodos p ON p.id = pr.periodo_id
            WHERE nr.aluno_id = ? AND nr.tenant_id = ?
        ');
        $recDb->execute([$alunoId, $tenantId]);
        foreach ($recDb->fetchAll() as $rn) {
            $perNome = $rn['per_nome'];
            $discNome = $rn['disc_nome'];
            if (isset($boletim[$perNome][$discNome])) {
                $boletim[$perNome][$discNome]['nota_rec'] = $rn['nota'];
                $boletim[$perNome][$discNome]['rec_substituiu'] = $rn['nota_substituiu'];
                if ($rn['nota_substituiu']) {
                    $mediaOriginal = $boletim[$perNome][$discNome]['media'];
                    $boletim[$perNome][$discNome]['media_base'] = $mediaOriginal;
                    $boletim[$perNome][$discNome]['media'] = $rn['nota'];
                    $boletim[$perNome][$discNome]['status'] = $mediaService->statusAluno($rn['nota'], (float) ($config['nota_minima_aprovacao'] ?? 6.0));
                }
            }
        }

        View::render('notas/boletim', [
            'pageTitle' => "Boletim — {$aluno['nome_completo']}",
            'aluno'     => $aluno,
            'matricula' => $matricula,
            'boletim'   => $boletim,
        ]);
    }
}
