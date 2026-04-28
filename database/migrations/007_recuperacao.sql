-- 006_recuperacao.sql

-- Períodos de Recuperação definidos pela Coordenação (ex: Recuperação do 1º Bimestre ou Recuperação Final)
CREATE TABLE IF NOT EXISTS periodos_recuperacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    ano_letivo_id INT NOT NULL,
    periodo_id INT NULL, -- Se NULL, é uma recuperação global/final. Se PREENCHIDO, é uma recuperação paralela daquele bimestre
    nome VARCHAR(100) NOT NULL, -- Ex: "Recuperação do 1º Bimestre"
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    aberto_por INT NOT NULL,
    aberto_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE SET NULL,
    FOREIGN KEY (aberto_por) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- As notas que os alunos tiraram durante esse período específico
CREATE TABLE IF NOT EXISTS notas_recuperacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    periodo_recuperacao_id INT NOT NULL,
    aluno_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    turma_id INT NOT NULL,
    nota DECIMAL(5,2) NULL,
    lancado_por INT NOT NULL,
    lancado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    nota_substituiu TINYINT(1) DEFAULT 0, -- 1 se a nota de recuperação se tornou MAIOR que a original/antiga, servindo como média nova
    nota_anterior DECIMAL(5,2) NULL,      -- Guarda qual era a nota prévia para efeito de histórico base
    observacao VARCHAR(255) NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_recuperacao_id) REFERENCES periodos_recuperacao(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
    FOREIGN KEY (lancado_por) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uni_recuperacao_aluno_disc (periodo_recuperacao_id, aluno_id, disciplina_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
