-- Faz parte do planejamento da Plataforma SaaS EDU - Fase 5
-- Registra eventos diários, ocorrências ou elogios que impactam na vida acadêmica de um aluno específico.

CREATE TABLE IF NOT EXISTS diario_anotacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    aluno_id INT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    conteudo TEXT NOT NULL,
    categoria ENUM('comportamento','aprendizado','elogio','saude','familiar','outro') DEFAULT 'comportamento',
    visibilidade ENUM('somente_autor','professores','coordenacao','pais','todos') DEFAULT 'coordenacao',
    ano_letivo_id INT UNSIGNED NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    editado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ano_letivo_id) REFERENCES anos_letivos(id) ON DELETE SET NULL
);
