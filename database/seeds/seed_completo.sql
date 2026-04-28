-- seed_completo.sql
-- Dados completos para desenvolvimento e testes
-- ATENÇÃO: NÃO executar em produção!

-- ============================================================
-- TENANT E CONFIGURAÇÕES
-- ============================================================

INSERT INTO tenants (id, nome, slug, ativo, plano) VALUES
(1, 'Escola Estadual Demo', 'escola-demo', 1, 'profissional');

INSERT INTO tenant_visual (tenant_id, cor_primaria, cor_secundaria, cor_texto, tema_padrao) VALUES
(1, '#1e40af', '#3b82f6', '#ffffff', 'claro');

INSERT INTO tenant_academico (
    tenant_id, tipo_periodo, qtd_periodos, qtd_avaliacoes_por_periodo,
    formula_media, nota_minima_aprovacao, percentual_maximo_faltas, 
    recuperacao_automatica, plano_aula_habilitado
) VALUES (1, 'bimestre', 4, 2, 'simples', 6.00, 25, 1, 0);

-- ============================================================
-- USUÁRIOS (senha: Admin@123 para todos)
-- ============================================================

-- Super Admin
INSERT INTO users (id, tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(1, 1, 'Super Administrador', 'admin@edusaas.com', '00000000000',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- Diretor
INSERT INTO users (id, tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(2, 1, 'Maria Diretora Silva', 'diretor@escola-demo.com', '11111111111',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'diretor');

-- Coordenador
INSERT INTO users (id, tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(3, 1, 'João Coordenador Santos', 'coordenador@escola-demo.com', '22222222222',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordenador');

-- Professora de Matemática
INSERT INTO users (id, tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(4, 1, 'Ana Paula Professora', 'professora@escola-demo.com', '33333333333',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor');

-- Professor de Português
INSERT INTO users (id, tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(5, 1, 'Carlos Professor Oliveira', 'professor2@escola-demo.com', '44444444444',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor');

-- Secretária
INSERT INTO users (id, tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(6, 1, 'Beatriz Secretaria', 'secretaria@escola-demo.com', '55555555555',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretaria');

-- ============================================================
-- ANO LETIVO E PERÍODOS
-- ============================================================

INSERT INTO anos_letivos (id, tenant_id, nome, data_inicio, data_fim, ativo) VALUES
(1, 1, '2025', '2025-02-01', '2025-12-15', 1);

INSERT INTO periodos (id, tenant_id, ano_letivo_id, nome, numero, data_inicio, data_fim) VALUES
(1, 1, 1, '1º Bimestre', 1, '2025-02-01', '2025-04-30'),
(2, 1, 1, '2º Bimestre', 2, '2025-05-01', '2025-07-15'),
(3, 1, 1, '3º Bimestre', 3, '2025-08-01', '2025-10-15'),
(4, 1, 1, '4º Bimestre', 4, '2025-10-16', '2025-12-15');

-- ============================================================
-- DISCIPLINAS
-- ============================================================

INSERT INTO disciplinas (id, tenant_id, nome, carga_horaria_semanal, cor_icone) VALUES
(1, 1, 'Matemática',          4, '#ef4444'),
(2, 1, 'Português',           4, '#3b82f6'),
(3, 1, 'Ciências',            2, '#22c55e'),
(4, 1, 'História',            2, '#f97316'),
(5, 1, 'Geografia',           2, '#a855f7'),
(6, 1, 'Inglês',              2, '#06b6d4'),
(7, 1, 'Educação Física',     2, '#eab308'),
(8, 1, 'Artes',               1, '#ec4899');

-- ============================================================
-- TURMAS
-- ============================================================

INSERT INTO turmas (id, tenant_id, ano_letivo_id, nome, serie, turno, max_alunos) VALUES
(1, 1, 1, '9º A', '9º Ano', 'manha',  30),
(2, 1, 1, '9º B', '9º Ano', 'tarde',  30),
(3, 1, 1, '8º A', '8º Ano', 'manha',  30);

-- ============================================================
-- VÍNCULOS PROFESSOR-DISCIPLINA-TURMA
-- ============================================================

-- Ana Paula: Matemática em todas as turmas
INSERT INTO professor_disciplina_turma (tenant_id, user_id, disciplina_id, turma_id, ano_letivo_id) VALUES
(1, 4, 1, 1, 1),
(1, 4, 1, 2, 1),
(1, 4, 1, 3, 1);

-- Carlos: Português em todas as turmas
INSERT INTO professor_disciplina_turma (tenant_id, user_id, disciplina_id, turma_id, ano_letivo_id) VALUES
(1, 5, 2, 1, 1),
(1, 5, 2, 2, 1),
(1, 5, 2, 3, 1);

-- ============================================================
-- ALUNOS
-- ============================================================

INSERT INTO alunos (id, tenant_id, nome_completo, data_nascimento) VALUES
(1,  1, 'Carlos Eduardo Silva Santos',      '2009-03-15'),
(2,  1, 'Beatriz Santos Lima',              '2009-07-22'),
(3,  1, 'Gabriel Oliveira Costa',           '2010-01-08'),
(4,  1, 'Letícia Ferreira Souza',           '2009-11-30'),
(5,  1, 'Pedro Henrique Alves',             '2010-04-17'),
(6,  1, 'Maria Eduarda Rodrigues',          '2009-09-05'),
(7,  1, 'Lucas Gabriel Martins',            '2009-12-12'),
(8,  1, 'Ana Clara Pereira',                '2010-02-28'),
(9,  1, 'Rafael Souza Lima',                '2009-06-19'),
(10, 1, 'Júlia Fernandes Costa',            '2009-08-07'),
(11, 1, 'Bruno Henrique Santos',            '2010-03-21'),
(12, 1, 'Isabela Cristina Silva',           '2009-10-14'),
(13, 1, 'Gustavo Almeida Rocha',            '2009-05-25'),
(14, 1, 'Larissa Oliveira Dias',            '2010-01-30'),
(15, 1, 'Vinícius Costa Ribeiro',           '2009-11-08');

-- ============================================================
-- MATRÍCULAS
-- ============================================================

-- 9º A (5 alunos)
INSERT INTO matriculas (tenant_id, aluno_id, turma_id, ano_letivo_id, numero_matricula, data_matricula, status) VALUES
(1, 1,  1, 1, '2025001', '2025-02-01', 'ativo'),
(1, 2,  1, 1, '2025002', '2025-02-01', 'ativo'),
(1, 3,  1, 1, '2025003', '2025-02-01', 'ativo'),
(1, 4,  1, 1, '2025004', '2025-02-01', 'ativo'),
(1, 5,  1, 1, '2025005', '2025-02-01', 'ativo');

-- 9º B (5 alunos)
INSERT INTO matriculas (tenant_id, aluno_id, turma_id, ano_letivo_id, numero_matricula, data_matricula, status) VALUES
(1, 6,  2, 1, '2025006', '2025-02-01', 'ativo'),
(1, 7,  2, 1, '2025007', '2025-02-01', 'ativo'),
(1, 8,  2, 1, '2025008', '2025-02-01', 'ativo'),
(1, 9,  2, 1, '2025009', '2025-02-01', 'ativo'),
(1, 10, 2, 1, '2025010', '2025-02-01', 'ativo');

-- 8º A (5 alunos)
INSERT INTO matriculas (tenant_id, aluno_id, turma_id, ano_letivo_id, numero_matricula, data_matricula, status) VALUES
(1, 11, 3, 1, '2025011', '2025-02-01', 'ativo'),
(1, 12, 3, 1, '2025012', '2025-02-01', 'ativo'),
(1, 13, 3, 1, '2025013', '2025-02-01', 'ativo'),
(1, 14, 3, 1, '2025014', '2025-02-01', 'ativo'),
(1, 15, 3, 1, '2025015', '2025-02-01', 'ativo');

-- ============================================================
-- RESPONSÁVEIS (senha padrão: últimos 4 CPF + @escola-demo)
-- ============================================================

INSERT INTO responsaveis (tenant_id, aluno_id, nome_completo, parentesco, cpf, telefone, email, senha_portal_hash, contato_emergencia) VALUES
(1, 1, 'José Carlos Silva', 'pai', '12345678901', '(11) 98765-4321', 'jose@email.com',
 '$2y$12$XWZp3R9qNM8y.E9jQk9P1uka.z8H.iGlPY/dBUxW5TqGEd4TyN5Tm', 1),
-- Senha: 8901@escola-demo

(1, 2, 'Márcia Santos Lima', 'mae', '23456789012', '(11) 97654-3210', 'marcia@email.com',
 '$2y$12$XWZp3R9qNM8y.E9jQk9P1uka.z8H.iGlPY/dBUxW5TqGEd4TyN5Tm', 1),
-- Senha: 9012@escola-demo

(1, 3, 'Roberto Oliveira', 'pai', '34567890123', '(11) 96543-2109', 'roberto@email.com',
 '$2y$12$XWZp3R9qNM8y.E9jQk9P1uka.z8H.iGlPY/dBUxW5TqGEd4TyN5Tm', 1),
-- Senha: 0123@escola-demo

(1, 4, 'Fernanda Ferreira', 'mae', '45678901234', '(11) 95432-1098', 'fernanda@email.com',
 '$2y$12$XWZp3R9qNM8y.E9jQk9P1uka.z8H.iGlPY/dBUxW5TqGEd4TyN5Tm', 1),
-- Senha: 1234@escola-demo

(1, 5, 'Paulo Henrique Alves', 'pai', '56789012345', '(11) 94321-0987', 'paulo@email.com',
 '$2y$12$XWZp3R9qNM8y.E9jQk9P1uka.z8H.iGlPY/dBUxW5TqGEd4TyN5Tm', 1);
-- Senha: 2345@escola-demo

-- ============================================================
-- AVALIAÇÕES (AV1 e AV2 para cada período)
-- ============================================================

-- 9º A - Matemática - 1º Bimestre
INSERT INTO avaliacoes (tenant_id, disciplina_id, turma_id, periodo_id, nome, peso, nota_maxima, ordem) VALUES
(1, 1, 1, 1, 'AV1', 1.0, 10.0, 1),
(1, 1, 1, 1, 'AV2', 1.0, 10.0, 2);

-- 9º A - Português - 1º Bimestre
INSERT INTO avaliacoes (tenant_id, disciplina_id, turma_id, periodo_id, nome, peso, nota_maxima, ordem) VALUES
(1, 2, 1, 1, 'AV1', 1.0, 10.0, 1),
(1, 2, 1, 1, 'AV2', 1.0, 10.0, 2);

-- ============================================================
-- NOTAS DE EXEMPLO (30% preenchidas)
-- ============================================================

-- Carlos Eduardo - Matemática
INSERT INTO notas (tenant_id, avaliacao_id, aluno_id, nota, lancado_por) VALUES
(1, 1, 1, 8.5, 4), -- AV1
(1, 2, 1, 9.0, 4); -- AV2

-- Beatriz Santos - Matemática
INSERT INTO notas (tenant_id, avaliacao_id, aluno_id, nota, lancado_por) VALUES
(1, 1, 2, 7.0, 4),
(1, 2, 2, 8.5, 4);

-- Gabriel Oliveira - Matemática (apenas AV1)
INSERT INTO notas (tenant_id, avaliacao_id, aluno_id, nota, lancado_por) VALUES
(1, 1, 3, 6.5, 4);

-- ============================================================
-- AULAS E FREQUÊNCIA DE EXEMPLO
-- ============================================================

-- Aula de Matemática 9ºA dia 10/03
INSERT INTO aulas (id, tenant_id, disciplina_id, turma_id, periodo_id, data, numero_aulas, conteudo_dado, criado_por) VALUES
(1, 1, 1, 1, 1, '2025-03-10', 2, 'Equações do 2º grau - introdução', 4);

-- Frequência dessa aula
INSERT INTO frequencias (tenant_id, aula_id, aluno_id, presente) VALUES
(1, 1, 1, 1), -- Carlos presente
(1, 1, 2, 1), -- Beatriz presente
(1, 1, 3, 0), -- Gabriel faltou
(1, 1, 4, 1), -- Letícia presente
(1, 1, 5, 1); -- Pedro presente

-- ============================================================
-- COMUNICADOS
-- ============================================================

INSERT INTO comunicados (tenant_id, titulo, conteudo, publico_alvo, criado_por, fixado) VALUES
(1, 'Bem-vindos ao ano letivo 2025!', 
    'Prezados pais e alunos,\n\nÉ com grande alegria que iniciamos mais um ano letivo. Desejamos a todos um ano repleto de aprendizado e conquistas!\n\nAtenciosamente,\nDireção',
    'todos', 2, 1);

INSERT INTO comunicados (tenant_id, titulo, conteudo, publico_alvo, criado_por) VALUES
(1, 'Reunião de Pais - 1º Bimestre', 
    'Informamos que a reunião de pais do 1º bimestre acontecerá no dia 15/04/2025 às 19h no auditório da escola.\n\nContamos com a presença de todos!',
    'pais', 2);

-- ============================================================
-- FIM DO SEED
-- ============================================================
