-- 006_comunicacao.sql

-- Eventos / Agenda escolar
CREATE TABLE IF NOT EXISTS eventos (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    ano_letivo_id  INT UNSIGNED DEFAULT NULL,
    titulo         VARCHAR(200) NOT NULL,
    descricao      TEXT         DEFAULT NULL,
    tipo           ENUM('reuniao','evento','feriado','sem_aula','prova','outro') NOT NULL DEFAULT 'outro',
    data_inicio    DATETIME     NOT NULL,
    data_fim       DATETIME     DEFAULT NULL,
    dia_todo       TINYINT(1)   NOT NULL DEFAULT 1,
    turma_id       INT UNSIGNED DEFAULT NULL COMMENT 'NULL = toda a escola',
    cor            VARCHAR(7)   DEFAULT '#3b82f6',
    criado_por     INT UNSIGNED NOT NULL,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant     (tenant_id),
    INDEX idx_data       (data_inicio),
    INDEX idx_turma      (turma_id),
    FOREIGN KEY (tenant_id)    REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (criado_por)   REFERENCES users(id)        ON DELETE RESTRICT,
    FOREIGN KEY (turma_id)     REFERENCES turmas(id)       ON DELETE SET NULL,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comunicados / Mural de avisos
CREATE TABLE IF NOT EXISTS comunicados (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    titulo              VARCHAR(200) NOT NULL,
    conteudo            TEXT         NOT NULL,
    arquivo_path        VARCHAR(300) DEFAULT NULL,
    publico_alvo        ENUM('todos','professores','pais','turma') NOT NULL DEFAULT 'todos',
    turma_id            INT UNSIGNED DEFAULT NULL,
    requer_confirmacao  TINYINT(1)   NOT NULL DEFAULT 0,
    data_expiracao      DATE         DEFAULT NULL,
    fixado              TINYINT(1)   NOT NULL DEFAULT 0,
    criado_por          INT UNSIGNED NOT NULL,
    criado_em           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant   (tenant_id),
    INDEX idx_criado   (criado_em),
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id)  ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES users(id)    ON DELETE RESTRICT,
    FOREIGN KEY (turma_id)   REFERENCES turmas(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Confirmações de leitura de comunicados
CREATE TABLE IF NOT EXISTS comunicados_leitura (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunicado_id    INT UNSIGNED NOT NULL,
    user_id          INT UNSIGNED DEFAULT NULL,
    responsavel_id   INT UNSIGNED DEFAULT NULL,
    lido_em          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_leitura_user  (comunicado_id, user_id),
    UNIQUE KEY uq_leitura_resp  (comunicado_id, responsavel_id),
    FOREIGN KEY (comunicado_id)  REFERENCES comunicados(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notificações internas
CREATE TABLE IF NOT EXISTS notificacoes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED DEFAULT NULL,
    papel_alvo VARCHAR(50)  DEFAULT NULL COMMENT 'ex: professor — notifica todos do papel',
    titulo     VARCHAR(200) NOT NULL,
    mensagem   TEXT         NOT NULL,
    link       VARCHAR(300) DEFAULT NULL,
    lida       TINYINT(1)   NOT NULL DEFAULT 0,
    criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant  (tenant_id),
    INDEX idx_user    (user_id),
    INDEX idx_lida    (lida),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log de auditoria
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED DEFAULT NULL,
    user_id        INT UNSIGNED DEFAULT NULL,
    acao           VARCHAR(100) NOT NULL COMMENT 'ex: nota.editou, recuperacao.abriu',
    entidade_tipo  VARCHAR(50)  NOT NULL,
    entidade_id    INT UNSIGNED DEFAULT NULL,
    dados_antes    JSON         DEFAULT NULL,
    dados_depois   JSON         DEFAULT NULL,
    ip             VARCHAR(45)  DEFAULT NULL,
    user_agent     VARCHAR(300) DEFAULT NULL,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant   (tenant_id),
    INDEX idx_user     (user_id),
    INDEX idx_entidade (entidade_tipo, entidade_id),
    INDEX idx_acao     (acao),
    INDEX idx_criado   (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2FA tokens temporários
CREATE TABLE IF NOT EXISTS sessions_2fa (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    codigo_hash VARCHAR(255) NOT NULL,
    expira_em   DATETIME     NOT NULL,
    usado       TINYINT(1)   NOT NULL DEFAULT 0,
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
