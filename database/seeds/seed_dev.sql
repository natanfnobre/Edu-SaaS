-- seed_dev.sql
-- Dados de desenvolvimento. NÃO executar em produção.

-- Tenant de exemplo
INSERT INTO tenants (nome, slug, ativo, plano) VALUES
('Escola Estadual Demo', 'escola-demo', 1, 'profissional');

-- Configuração visual
INSERT INTO tenant_visual (tenant_id, cor_primaria, cor_secundaria, cor_texto, tema_padrao) VALUES
(1, '#1e40af', '#3b82f6', '#ffffff', 'claro');

-- Configuração acadêmica
INSERT INTO tenant_academico (
    tenant_id, tipo_periodo, qtd_periodos, qtd_avaliacoes_por_periodo,
    formula_media, nota_minima_aprovacao, percentual_maximo_faltas, recuperacao_automatica
) VALUES (1, 'bimestre', 4, 2, 'simples', 6.00, 25, 1);

-- Super Admin (senha: Admin@123)
INSERT INTO users (tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(1, 'Super Administrador', 'admin@edusaas.com', '00000000000',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@123
 'super_admin');

-- Diretor
INSERT INTO users (tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(1, 'Maria Diretora', 'diretor@escola-demo.com', '11111111111',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'diretor');

-- Coordenador
INSERT INTO users (tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(1, 'João Coordenador', 'coordenador@escola-demo.com', '22222222222',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'coordenador');

-- Professor (como sua esposa!)
INSERT INTO users (tenant_id, nome, email, cpf, senha_hash, papel) VALUES
(1, 'Ana Paula Professora', 'professora@escola-demo.com', '33333333333',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'professor');

-- Ano letivo
INSERT INTO anos_letivos (tenant_id, nome, data_inicio, data_fim, ativo) VALUES
(1, '2025', '2025-02-01', '2025-12-15', 1);

-- Turmas
INSERT INTO turmas (tenant_id, ano_letivo_id, nome, serie, turno) VALUES
(1, 1, '9º A', '9º Ano', 'manha'),
(1, 1, '9º B', '9º Ano', 'tarde'),
(1, 1, '8º A', '8º Ano', 'manha');

-- Disciplinas
INSERT INTO disciplinas (tenant_id, nome, carga_horaria_semanal, cor_icone) VALUES
(1, 'Matemática',          4, '#ef4444'),
(1, 'Português',           4, '#3b82f6'),
(1, 'Ciências',            2, '#22c55e'),
(1, 'História',            2, '#f97316'),
(1, 'Geografia',           2, '#a855f7'),
(1, 'Inglês',              2, '#06b6d4'),
(1, 'Educação Física',     2, '#eab308'),
(1, 'Artes',               1, '#ec4899');

-- Vínculos professor -> disciplina -> turma
INSERT INTO professor_disciplina_turma (tenant_id, user_id, disciplina_id, turma_id, ano_letivo_id) VALUES
(1, 4, 1, 1, 1), -- Ana Paula: Matemática no 9ºA
(1, 4, 1, 2, 1), -- Ana Paula: Matemática no 9ºB
(1, 4, 1, 3, 1); -- Ana Paula: Matemática no 8ºA

-- Períodos (4 bimestres)
INSERT INTO periodos (tenant_id, ano_letivo_id, nome, numero, data_inicio, data_fim) VALUES
(1, 1, '1º Bimestre', 1, '2025-02-01', '2025-04-30'),
(1, 1, '2º Bimestre', 2, '2025-05-01', '2025-07-15'),
(1, 1, '3º Bimestre', 3, '2025-08-01', '2025-10-15'),
(1, 1, '4º Bimestre', 4, '2025-10-16', '2025-12-15');

-- Alunos de exemplo
INSERT INTO alunos (tenant_id, nome_completo, data_nascimento) VALUES
(1, 'Carlos Eduardo Silva',   '2009-03-15'),
(1, 'Beatriz Santos Lima',    '2009-07-22'),
(1, 'Gabriel Oliveira Costa', '2010-01-08'),
(1, 'Letícia Ferreira Souza', '2009-11-30'),
(1, 'Pedro Henrique Alves',   '2010-04-17');

-- Matrículas no 9ºA
INSERT INTO matriculas (tenant_id, aluno_id, turma_id, ano_letivo_id, numero_matricula, data_matricula) VALUES
(1, 1, 1, 1, '2025001', '2025-02-01'),
(1, 2, 1, 1, '2025002', '2025-02-01'),
(1, 3, 1, 1, '2025003', '2025-02-01'),
(1, 4, 1, 1, '2025004', '2025-02-01'),
(1, 5, 1, 1, '2025005', '2025-02-01');
