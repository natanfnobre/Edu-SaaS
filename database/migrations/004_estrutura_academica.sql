-- 004_estrutura_academica.sql

-- Anos Letivos
CREATE TABLE IF NOT EXISTS anos_letivos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id     INT UNSIGNED NOT NULL,
    nome          VARCHAR(20)  NOT NULL COMMENT 'ex: 2024, 2024/2025',
    data_inicio   DATE         NOT NULL,
    data_fim      DATE         NOT NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Turmas
CREATE TABLE IF NOT EXISTS turmas (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    ano_letivo_id  INT UNSIGNED NOT NULL,
    nome           VARCHAR(50)  NOT NULL COMMENT 'ex: 9º A, 1ª Série B',
    serie          VARCHAR(50)  DEFAULT NULL,
    turno          ENUM('manha','tarde','noite','integral') NOT NULL DEFAULT 'manha',
    sala           VARCHAR(20)  DEFAULT NULL,
    max_alunos     TINYINT UNSIGNED DEFAULT NULL,
    ativo          TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant      (tenant_id),
    INDEX idx_ano_letivo  (ano_letivo_id),
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)       ON DELETE CASCADE,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Disciplinas
CREATE TABLE IF NOT EXISTS disciplinas (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id            INT UNSIGNED NOT NULL,
    nome                 VARCHAR(100) NOT NULL,
    carga_horaria_semanal TINYINT UNSIGNED DEFAULT NULL,
    cor_icone            VARCHAR(7)   DEFAULT '#6366f1',
    ativo                TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vínculo Professor <-> Disciplina <-> Turma
CREATE TABLE IF NOT EXISTS professor_disciplina_turma (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    user_id        INT UNSIGNED NOT NULL COMMENT 'Professor',
    disciplina_id  INT UNSIGNED NOT NULL,
    turma_id       INT UNSIGNED NOT NULL,
    ano_letivo_id  INT UNSIGNED NOT NULL,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_vinculo (user_id, disciplina_id, turma_id, ano_letivo_id),
    INDEX idx_tenant      (tenant_id),
    INDEX idx_professor   (user_id),
    INDEX idx_turma       (turma_id),
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)       ON DELETE CASCADE,
    FOREIGN KEY (user_id)       REFERENCES users(id)         ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)   ON DELETE CASCADE,
    FOREIGN KEY (turma_id)      REFERENCES turmas(id)        ON DELETE CASCADE,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Períodos letivos (bimestres, trimestres)
CREATE TABLE IF NOT EXISTS periodos (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        INT UNSIGNED NOT NULL,
    ano_letivo_id    INT UNSIGNED NOT NULL,
    nome             VARCHAR(50)  NOT NULL COMMENT 'ex: 1º Bimestre',
    numero           TINYINT UNSIGNED NOT NULL,
    data_inicio      DATE         NOT NULL,
    data_fim         DATE         NOT NULL,
    notas_bloqueadas TINYINT(1)   NOT NULL DEFAULT 0,
    bloqueado_em     DATETIME     DEFAULT NULL,
    bloqueado_por    INT UNSIGNED DEFAULT NULL,
    criado_em        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant      (tenant_id),
    INDEX idx_ano_letivo  (ano_letivo_id),
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id) ON DELETE CASCADE,
    FOREIGN KEY (bloqueado_por) REFERENCES users(id)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Avaliações configuradas por disciplina/turma/período
CREATE TABLE IF NOT EXISTS avaliacoes (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    disciplina_id  INT UNSIGNED NOT NULL,
    turma_id       INT UNSIGNED NOT NULL,
    periodo_id     INT UNSIGNED NOT NULL,
    nome           VARCHAR(50)  NOT NULL COMMENT 'AV1, AV2, Prova Final...',
    peso           DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    nota_maxima    DECIMAL(4,2) NOT NULL DEFAULT 10.00,
    ordem          TINYINT UNSIGNED NOT NULL DEFAULT 1,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant     (tenant_id),
    INDEX idx_disciplina (disciplina_id),
    INDEX idx_turma      (turma_id),
    INDEX idx_periodo    (periodo_id),
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)  ON DELETE CASCADE,
    FOREIGN KEY (turma_id)      REFERENCES turmas(id)        ON DELETE CASCADE,
    FOREIGN KEY (periodo_id)    REFERENCES periodos(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
