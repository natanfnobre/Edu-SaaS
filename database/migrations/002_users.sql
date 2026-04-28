-- 002_users.sql
-- Usuários do sistema (todos os papéis exceto pais)

CREATE TABLE IF NOT EXISTS users (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         INT UNSIGNED NOT NULL,
    nome              VARCHAR(150) NOT NULL,
    email             VARCHAR(200) NOT NULL,
    cpf               VARCHAR(11)  DEFAULT NULL,
    senha_hash        VARCHAR(255) NOT NULL,
    papel             ENUM('super_admin','diretor','coordenador','secretaria','professor') NOT NULL,
    tema_preferido    ENUM('claro','escuro','sistema') NOT NULL DEFAULT 'sistema',
    foto_path         VARCHAR(300) DEFAULT NULL,
    telefone          VARCHAR(20)  DEFAULT NULL,
    dois_fa_ativo     TINYINT(1)   NOT NULL DEFAULT 0,
    dois_fa_segredo   VARCHAR(64)  DEFAULT NULL,
    trocar_senha      TINYINT(1)   NOT NULL DEFAULT 0,
    ultimo_acesso     DATETIME     DEFAULT NULL,
    ativo             TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email_tenant (email, tenant_id),
    INDEX idx_tenant  (tenant_id),
    INDEX idx_papel   (papel),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
