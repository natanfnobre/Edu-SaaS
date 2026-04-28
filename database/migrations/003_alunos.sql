-- 003_alunos.sql

CREATE TABLE IF NOT EXISTS alunos (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               INT UNSIGNED NOT NULL,
    nome_completo           VARCHAR(200) NOT NULL,
    data_nascimento         DATE         DEFAULT NULL,
    foto_path               VARCHAR(300) DEFAULT NULL,
    rg                      VARCHAR(20)  DEFAULT NULL,
    cpf                     VARCHAR(11)  DEFAULT NULL,
    naturalidade            VARCHAR(100) DEFAULT NULL,
    nacionalidade           VARCHAR(100) DEFAULT NULL COMMENT 'padrão: Brasileira',
    necessidades_especiais  TEXT         DEFAULT NULL,
    observacoes_medicas     TEXT         DEFAULT NULL,
    ativo                   TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em               DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em           DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant        (tenant_id),
    INDEX idx_nome          (nome_completo),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Endereços (compartilhado entre aluno e responsável)
CREATE TABLE IF NOT EXISTS enderecos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidade_tipo   ENUM('aluno','responsavel') NOT NULL,
    entidade_id     INT UNSIGNED NOT NULL,
    logradouro      VARCHAR(200) DEFAULT NULL,
    numero          VARCHAR(20)  DEFAULT NULL,
    complemento     VARCHAR(100) DEFAULT NULL,
    bairro          VARCHAR(100) DEFAULT NULL,
    cidade          VARCHAR(100) DEFAULT NULL,
    estado          CHAR(2)      DEFAULT NULL,
    cep             VARCHAR(9)   DEFAULT NULL,
    INDEX idx_entidade (entidade_tipo, entidade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Responsáveis / Pais
CREATE TABLE IF NOT EXISTS responsaveis (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    aluno_id            INT UNSIGNED NOT NULL,
    nome_completo       VARCHAR(200) NOT NULL,
    parentesco          ENUM('pai','mae','avo','avoa','tio','tia','responsavel','outro') NOT NULL DEFAULT 'responsavel',
    cpf                 VARCHAR(11)  DEFAULT NULL,
    rg                  VARCHAR(20)  DEFAULT NULL,
    telefone            VARCHAR(20)  DEFAULT NULL,
    telefone2           VARCHAR(20)  DEFAULT NULL,
    email               VARCHAR(200) DEFAULT NULL,
    profissao           VARCHAR(100) DEFAULT NULL,
    local_trabalho      VARCHAR(200) DEFAULT NULL,
    senha_portal_hash   VARCHAR(255) DEFAULT NULL COMMENT 'Acesso ao portal dos pais',
    trocar_senha        TINYINT(1)   NOT NULL DEFAULT 1,
    pode_buscar_aluno   TINYINT(1)   NOT NULL DEFAULT 1,
    contato_emergencia  TINYINT(1)   NOT NULL DEFAULT 0,
    ativo               TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant    (tenant_id),
    INDEX idx_aluno     (aluno_id),
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)   REFERENCES alunos(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Matrículas
CREATE TABLE IF NOT EXISTS matriculas (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        INT UNSIGNED NOT NULL,
    aluno_id         INT UNSIGNED NOT NULL,
    turma_id         INT UNSIGNED NOT NULL,
    ano_letivo_id    INT UNSIGNED NOT NULL,
    numero_matricula VARCHAR(30)  DEFAULT NULL,
    data_matricula   DATE         NOT NULL,
    status           ENUM('ativo','transferido','cancelado','concluido') NOT NULL DEFAULT 'ativo',
    escola_origem    VARCHAR(200) DEFAULT NULL,
    escola_destino   VARCHAR(200) DEFAULT NULL,
    observacoes      TEXT         DEFAULT NULL,
    criado_em        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_aluno_turma_ano (aluno_id, turma_id, ano_letivo_id),
    INDEX idx_tenant      (tenant_id),
    INDEX idx_aluno       (aluno_id),
    INDEX idx_turma       (turma_id),
    INDEX idx_ano_letivo  (ano_letivo_id),
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)      REFERENCES alunos(id)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
