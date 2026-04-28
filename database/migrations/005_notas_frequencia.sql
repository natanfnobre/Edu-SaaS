-- 005_notas_frequencia.sql

-- Notas dos alunos
CREATE TABLE IF NOT EXISTS notas (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    avaliacao_id   INT UNSIGNED NOT NULL,
    aluno_id       INT UNSIGNED NOT NULL,
    nota           DECIMAL(5,2) DEFAULT NULL,
    lancado_por    INT UNSIGNED NOT NULL,
    lancado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    editado_por    INT UNSIGNED DEFAULT NULL,
    editado_em     DATETIME     DEFAULT NULL,
    observacao     TEXT         DEFAULT NULL,
    UNIQUE KEY uq_nota (avaliacao_id, aluno_id),
    INDEX idx_tenant    (tenant_id),
    INDEX idx_avaliacao (avaliacao_id),
    INDEX idx_aluno     (aluno_id),
    FOREIGN KEY (tenant_id)    REFERENCES tenants(id)    ON DELETE CASCADE,
    FOREIGN KEY (avaliacao_id) REFERENCES avaliacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)     REFERENCES alunos(id)     ON DELETE CASCADE,
    FOREIGN KEY (lancado_por)  REFERENCES users(id)      ON DELETE RESTRICT,
    FOREIGN KEY (editado_por)  REFERENCES users(id)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aulas registradas (base da frequência)
CREATE TABLE IF NOT EXISTS aulas (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    disciplina_id  INT UNSIGNED NOT NULL,
    turma_id       INT UNSIGNED NOT NULL,
    periodo_id     INT UNSIGNED NOT NULL,
    data           DATE         NOT NULL,
    numero_aulas   TINYINT UNSIGNED NOT NULL DEFAULT 1,
    conteudo_dado  TEXT         DEFAULT NULL,
    criado_por     INT UNSIGNED NOT NULL,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant     (tenant_id),
    INDEX idx_turma      (turma_id),
    INDEX idx_disciplina (disciplina_id),
    INDEX idx_data       (data),
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)     ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id)      REFERENCES turmas(id)       ON DELETE CASCADE,
    FOREIGN KEY (periodo_id)    REFERENCES periodos(id)     ON DELETE CASCADE,
    FOREIGN KEY (criado_por)    REFERENCES users(id)        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Frequência por aluno por aula
CREATE TABLE IF NOT EXISTS frequencias (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    aula_id        INT UNSIGNED NOT NULL,
    aluno_id       INT UNSIGNED NOT NULL,
    presente       TINYINT(1)   NOT NULL DEFAULT 1,
    justificada    TINYINT(1)   NOT NULL DEFAULT 0,
    observacao     VARCHAR(300) DEFAULT NULL,
    UNIQUE KEY uq_frequencia (aula_id, aluno_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_aluno  (aluno_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)  ON DELETE CASCADE,
    FOREIGN KEY (aula_id)   REFERENCES aulas(id)    ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)  REFERENCES alunos(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anexos de atestado/justificativa de falta
CREATE TABLE IF NOT EXISTS anexos_falta (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    frequencia_id  INT UNSIGNED NOT NULL,
    aluno_id       INT UNSIGNED NOT NULL,
    arquivo_path   VARCHAR(300) NOT NULL,
    tipo_arquivo   VARCHAR(50)  DEFAULT NULL,
    descricao      VARCHAR(200) DEFAULT NULL,
    enviado_por    INT UNSIGNED NOT NULL,
    enviado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant     (tenant_id),
    INDEX idx_frequencia (frequencia_id),
    FOREIGN KEY (tenant_id)    REFERENCES tenants(id)     ON DELETE CASCADE,
    FOREIGN KEY (frequencia_id) REFERENCES frequencias(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)     REFERENCES alunos(id)      ON DELETE CASCADE,
    FOREIGN KEY (enviado_por)  REFERENCES users(id)       ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Períodos de recuperação
CREATE TABLE IF NOT EXISTS periodos_recuperacao (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    INT UNSIGNED NOT NULL,
    periodo_id   INT UNSIGNED NOT NULL,
    data_inicio  DATE         NOT NULL,
    data_fim     DATE         NOT NULL,
    aberto_por   INT UNSIGNED NOT NULL,
    aberto_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo        TINYINT(1)   NOT NULL DEFAULT 1,
    observacoes  TEXT         DEFAULT NULL,
    INDEX idx_tenant  (tenant_id),
    INDEX idx_periodo (periodo_id),
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id)  ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
    FOREIGN KEY (aberto_por) REFERENCES users(id)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notas de recuperação
CREATE TABLE IF NOT EXISTS notas_recuperacao (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id             INT UNSIGNED NOT NULL,
    periodo_recuperacao_id INT UNSIGNED NOT NULL,
    aluno_id              INT UNSIGNED NOT NULL,
    disciplina_id         INT UNSIGNED NOT NULL,
    nota                  DECIMAL(5,2) NOT NULL,
    nota_substituiu       TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = foi maior e substituiu',
    nota_anterior         DECIMAL(5,2) DEFAULT NULL COMMENT 'maior nota antes da recuperação',
    nota_final            DECIMAL(5,2) NOT NULL COMMENT 'nota que realmente vale',
    lancado_por           INT UNSIGNED NOT NULL,
    lancado_em            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_recup (periodo_recuperacao_id, aluno_id, disciplina_id),
    INDEX idx_tenant  (tenant_id),
    INDEX idx_aluno   (aluno_id),
    FOREIGN KEY (tenant_id)              REFERENCES tenants(id)              ON DELETE CASCADE,
    FOREIGN KEY (periodo_recuperacao_id) REFERENCES periodos_recuperacao(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)               REFERENCES alunos(id)               ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id)          REFERENCES disciplinas(id)           ON DELETE CASCADE,
    FOREIGN KEY (lancado_por)            REFERENCES users(id)                ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Diário de anotações por aluno
CREATE TABLE IF NOT EXISTS diario_anotacoes (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    aluno_id       INT UNSIGNED NOT NULL,
    autor_id       INT UNSIGNED NOT NULL,
    titulo         VARCHAR(200) NOT NULL,
    conteudo       TEXT         NOT NULL,
    categoria      ENUM('comportamento','aprendizado','elogio','saude','familiar','outro') NOT NULL DEFAULT 'outro',
    visibilidade   ENUM('somente_autor','professores','coordenacao','pais','todos') NOT NULL DEFAULT 'professores',
    ano_letivo_id  INT UNSIGNED DEFAULT NULL,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    editado_em     DATETIME     DEFAULT NULL,
    INDEX idx_tenant  (tenant_id),
    INDEX idx_aluno   (aluno_id),
    INDEX idx_autor   (autor_id),
    FOREIGN KEY (tenant_id)    REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)     REFERENCES alunos(id)       ON DELETE CASCADE,
    FOREIGN KEY (autor_id)     REFERENCES users(id)        ON DELETE RESTRICT,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
