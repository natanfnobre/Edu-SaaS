-- 001_tenants.sql
-- Escolas (tenants do SaaS)

CREATE TABLE IF NOT EXISTS tenants (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome                 VARCHAR(150) NOT NULL,
    slug                 VARCHAR(100) NOT NULL UNIQUE,
    dominio_personalizado VARCHAR(200) DEFAULT NULL UNIQUE,
    ativo                TINYINT(1) NOT NULL DEFAULT 1,
    plano                ENUM('basico','profissional','enterprise') NOT NULL DEFAULT 'basico',
    criado_em            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em        DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_dominio (dominio_personalizado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurações visuais por escola
CREATE TABLE IF NOT EXISTS tenant_visual (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         INT UNSIGNED NOT NULL UNIQUE,
    logo_path         VARCHAR(300) DEFAULT NULL,
    cor_primaria      VARCHAR(7)   NOT NULL DEFAULT '#1e40af',
    cor_secundaria    VARCHAR(7)   NOT NULL DEFAULT '#3b82f6',
    cor_texto         VARCHAR(7)   NOT NULL DEFAULT '#ffffff',
    fonte_primaria    VARCHAR(100) DEFAULT 'Nunito',
    tema_padrao       ENUM('claro','escuro') NOT NULL DEFAULT 'claro',
    rodape_pdf        TEXT DEFAULT NULL,
    cabecalho_pdf     TEXT DEFAULT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurações acadêmicas por escola
CREATE TABLE IF NOT EXISTS tenant_academico (
    id                           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                    INT UNSIGNED NOT NULL UNIQUE,
    tipo_periodo                 ENUM('bimestre','trimestre','semestre') NOT NULL DEFAULT 'bimestre',
    qtd_periodos                 TINYINT UNSIGNED NOT NULL DEFAULT 4,
    qtd_avaliacoes_por_periodo   TINYINT UNSIGNED NOT NULL DEFAULT 2,
    formula_media                ENUM('simples','ponderada','custom') NOT NULL DEFAULT 'simples',
    nota_minima_aprovacao        DECIMAL(4,2) NOT NULL DEFAULT 6.00,
    percentual_maximo_faltas     TINYINT UNSIGNED NOT NULL DEFAULT 25,
    plano_aula_habilitado        TINYINT(1) NOT NULL DEFAULT 0,
    recuperacao_automatica       TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
