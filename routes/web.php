<?php

use App\Helpers\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\TenantMiddleware;

// ── Rotas públicas (sem autenticação) ────────────────────────────

Router::group('', [TenantMiddleware::class], function () {

    Router::get('/login',       'AuthController@showLogin',  'login');
    Router::post('/login',      'AuthController@login');
    Router::get('/logout',      'AuthController@logout',     'logout');

    // Portal dos pais (login separado)
    Router::get('/pais/login',  'AuthController@showLoginPais', 'pais.login');
    Router::post('/pais/login', 'AuthController@loginPais');

});

// ── Rotas autenticadas ───────────────────────────────────────────

Router::group('', [TenantMiddleware::class, AuthMiddleware::class], function () {

    // Dashboard
    Router::get('/',          'DashboardController@index', 'home');
    Router::get('/dashboard', 'DashboardController@index', 'dashboard');
    Router::get('/relatorios/risco', 'DashboardController@riscos', 'relatorios.risco');

    // Perfil
    Router::get('/perfil',         'AuthController@perfil',        'perfil');
    Router::post('/perfil',        'AuthController@updatePerfil');
    Router::post('/perfil/senha',  'AuthController@updateSenha',   'perfil.senha');
    Router::post('/perfil/tema',   'AuthController@updateTema',    'perfil.tema');

    // ── Alunos ───────────────────────────────────────────────────
    Router::get('/alunos',                    'AlunoController@index',        'alunos.index');
    Router::get('/alunos/novo',               'AlunoController@create',       'alunos.create');
    Router::post('/alunos',                   'AlunoController@store',        'alunos.store');
    Router::get('/alunos/{id}',               'AlunoController@show',         'alunos.show');
    Router::get('/alunos/{id}/editar',        'AlunoController@edit',         'alunos.edit');
    Router::post('/alunos/{id}',              'AlunoController@update',       'alunos.update');
    Router::post('/alunos/{id}/deletar',      'AlunoController@destroy',      'alunos.destroy');
    Router::post('/alunos/matricula',         'AlunoController@matricular',    'alunos.matricula');
    Router::post('/alunos/transferir',        'AlunoController@transferir',    'alunos.transferir');
    Router::post('/alunos/matricula/cancelar','AlunoController@cancelarMatricula', 'alunos.matricula.cancelar');

    // Responsáveis
    Router::get('/alunos/{id}/responsaveis/novo',    'AlunoController@createResponsavel');
    Router::post('/alunos/{id}/responsaveis',         'AlunoController@storeResponsavel');
    Router::post('/alunos/{id}/responsaveis/{rid}',   'AlunoController@updateResponsavel');

    // ── Turmas ───────────────────────────────────────────────────
    Router::get('/turmas',              'TurmaController@index',  'turmas.index');
    Router::get('/turmas/nova',         'TurmaController@create', 'turmas.create');
    Router::post('/turmas',             'TurmaController@store');
    Router::get('/turmas/{id}',         'TurmaController@show',   'turmas.show');
    Router::get('/turmas/{id}/editar',  'TurmaController@edit',   'turmas.edit');
    Router::post('/turmas/{id}',        'TurmaController@update');

    // ── Notas ────────────────────────────────────────────────────
    Router::get('/notas',                   'NotaController@index',     'notas.index');
    Router::get('/notas/lancar/{turma_id}/{disciplina_id}/{periodo_id}', 'NotaController@lancar', 'notas.lancar');
    Router::post('/notas/lancar',           'NotaController@salvar',    'notas.salvar');
    Router::get('/notas/boletim/{aluno_id}','NotaController@boletim',   'notas.boletim');

    // ── Frequência ───────────────────────────────────────────────
    Router::get('/frequencia',                          'FrequenciaController@index',  'frequencia.index');
    Router::get('/frequencia/lancar/{turma_id}/{disciplina_id}', 'FrequenciaController@lancar', 'frequencia.lancar');
    Router::post('/frequencia/lancar',                  'FrequenciaController@salvar', 'frequencia.salvar');
    Router::post('/frequencia/justificar/{frequencia_id}', 'FrequenciaController@justificar');

    // ── Recuperação ──────────────────────────────────────────────
    Router::get('/recuperacao',                   'RecuperacaoController@index',  'recuperacao.index');
    Router::post('/recuperacao/abrir',            'RecuperacaoController@salvarPeriodo',  'recuperacao.abrir');
    Router::post('/recuperacao/fechar/{id}',      'RecuperacaoController@fechar');
    Router::get('/recuperacao/lancar/{periodo_recuperacao_id}/{turma_id}/{disciplina_id}', 'RecuperacaoController@lancar', 'recuperacao.lancar');
    Router::post('/recuperacao/lancar',           'RecuperacaoController@salvarNotas');

    // ── Diário ───────────────────────────────────────────────────
    Router::get('/diario/aluno/{aluno_id}',       'DiarioController@index',   'diario.index');
    Router::post('/diario/aluno/{aluno_id}',      'DiarioController@store',   'diario.store');
    Router::post('/diario/{id}/editar',           'DiarioController@update');
    Router::post('/diario/{id}/deletar',          'DiarioController@destroy');

    // ── Agenda ───────────────────────────────────────────────────
    Router::get('/agenda',               'AgendaController@index',  'agenda.index');
    Router::post('/agenda',              'AgendaController@store');
    Router::post('/agenda/{id}/editar',  'AgendaController@update');
    Router::post('/agenda/{id}/deletar', 'AgendaController@destroy');

    // ── Comunicados / Mural ───────────────────────────────────────
    Router::get('/comunicados',                  'MuralController@index',   'comunicados.index');
    Router::get('/comunicados/novo',             'MuralController@create',  'comunicados.create');
    Router::post('/comunicados',                 'MuralController@store');
    Router::post('/comunicados/{id}/confirmar',  'MuralController@confirmar');

    // ── Relatórios ───────────────────────────────────────────────
    Router::get('/relatorios',                        'RelatorioController@index',    'relatorios.index');
    Router::get('/relatorios/boletim/{aluno_id}',     'RelatorioController@boletim',  'relatorios.boletim');
    Router::get('/relatorios/turma/{turma_id}',       'RelatorioController@turma',    'relatorios.turma');
    Router::get('/relatorios/declaracao/{aluno_id}',  'RelatorioController@declaracao');
    Router::get('/relatorios/historico/{aluno_id}',   'RelatorioController@historico');

    // ── Config da escola (diretor/admin) ──────────────────────────
    Router::get('/configuracoes',           'TenantController@index',         'config.index');
    Router::post('/configuracoes/visual',   'TenantController@updateVisual',  'config.visual');
    Router::post('/configuracoes/academico','TenantController@updateAcademico');
    
    // Anos Letivos
    Router::post('/configuracoes/anos-letivos',          'TenantController@storeAnoLetivo');
    Router::post('/configuracoes/anos-letivos/{id}/ativar', 'TenantController@ativarAnoLetivo');

    // Disciplinas
    Router::post('/configuracoes/disciplinas',           'TenantController@storeDisciplina');
    Router::post('/configuracoes/disciplinas/{id}',      'TenantController@updateDisciplina');
    Router::post('/configuracoes/disciplinas/{id}/deletar', 'TenantController@destroyDisciplina');
    Router::get('/usuarios',                'TenantController@usuarios',      'usuarios.index');
    Router::post('/usuarios',               'TenantController@storeUsuario');
    Router::post('/usuarios/{id}',          'TenantController@updateUsuario');
    Router::post('/usuarios/{id}/deletar',  'TenantController@destroyUsuario');

    // ── Auditoria ─────────────────────────────────────────────────
    Router::get('/auditoria', 'TenantController@auditoria', 'auditoria.index');

});

// ── Portal dos Pais (autenticado separadamente) ──────────────────
Router::group('/pais', [TenantMiddleware::class], function () {
    Router::get('/',                      'PaisController@dashboard',   'pais.dashboard');
    Router::get('/boletim',               'PaisController@boletim',     'pais.boletim');
    Router::get('/frequencia',            'PaisController@frequencia',  'pais.frequencia');
    Router::get('/agenda',                'PaisController@agenda',      'pais.agenda');
    Router::get('/comunicados',           'PaisController@comunicados', 'pais.comunicados');
    Router::post('/comunicados/{id}/ler', 'PaisController@confirmarLeitura');
    Router::get('/diario',                'PaisController@diario',      'pais.diario');
    Router::get('/logout',                'AuthController@logoutPais',  'pais.logout');
});
