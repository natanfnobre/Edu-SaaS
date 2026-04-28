# Edu-SaaS
# 📚 EduSaaS — Sistema de Gestão Escolar

Sistema SaaS de gestão escolar multi-tenant focado em **mobile-first**, projetado para facilitar o dia a dia dos professores no lançamento de notas e frequência.

---

## ✨ Funcionalidades

### ✅ Fase 1 — Fundação (IMPLEMENTADA)

- ✅ Multi-tenant (uma instância, N escolas)
- ✅ Autenticação com roles (super_admin, diretor, coordenador, secretaria, professor, pai)
- ✅ Tema dinâmico por escola (cores, logo personalizáveis)
- ✅ Tema claro/escuro por usuário
- ✅ Layout mobile-first 100% responsivo
- ✅ Proteção CSRF, logs de auditoria, segurança completa
- ✅ Login separado para pais/responsáveis

### 🚧 Próximas Fases

- **Fase 2**: Cadastros (alunos, turmas, disciplinas, professores)
- **Fase 3**: Notas e Frequência
- **Fase 4**: Recuperação com substituição automática
- **Fase 5**: Diário de anotações
- **Fase 6**: Portal dos pais
- **Fase 7**: Agenda e comunicados
- **Fase 8**: Relatórios e PDFs
- **Fase 9**: Dashboard com indicadores
- **Fase 10**: Polimento e deploy

Ver detalhes completos em `PLANO_IMPLEMENTACAO.md`

---

## 🛠️ Stack Técnica

- **PHP 8.2+** (POO puro, sem frameworks)
- **MySQL 8.0+**
- **PDO** para queries seguras
- **HTML5 + CSS3** (mobile-first, sem frameworks CSS)
- **JavaScript Vanilla** (sem jQuery ou bibliotecas)

---

## 📦 Instalação

### 1. Requisitos

- PHP 8.2 ou superior
- MySQL 8.0 ou superior
- Composer (opcional, não usado no momento)
- Servidor web (Apache ou Nginx)

### 2. Clone o repositório

```bash
git clone <seu-repositorio>
cd edusaas
```

### 3. Configure o ambiente

```bash
cp .env.example .env
```

Edite o `.env` com suas credenciais de banco de dados:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=edusaas
DB_USER=root
DB_PASS=sua_senha_aqui
```

### 4. Crie o banco de dados

```bash
mysql -u root -p
```

```sql
CREATE DATABASE edusaas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Execute as migrations

**Na ordem:**

```bash
mysql -u root -p edusaas < database/migrations/001_tenants.sql
mysql -u root -p edusaas < database/migrations/002_users.sql
mysql -u root -p edusaas < database/migrations/003_alunos.sql
mysql -u root -p edusaas < database/migrations/004_estrutura_academica.sql
mysql -u root -p edusaas < database/migrations/005_notas_frequencia.sql
mysql -u root -p edusaas < database/migrations/006_comunicacao.sql
```

### 6. (Opcional) Carregue dados de desenvolvimento

```bash
mysql -u root -p edusaas < database/seeds/seed_dev.sql
```

Isso cria:
- **Tenant de exemplo:** Escola Estadual Demo (slug: `escola-demo`)
- **Super Admin:** admin@edusaas.com / Admin@123
- **Diretor:** diretor@escola-demo.com / Admin@123
- **Coordenador:** coordenador@escola-demo.com / Admin@123
- **Professora:** professora@escola-demo.com / Admin@123
- **5 alunos de exemplo** matriculados no 9º A
- **4 bimestres configurados** para o ano letivo 2025

### 7. Configure o servidor web

#### Apache (com mod_rewrite)

Aponte o DocumentRoot para `public/`:

```apache
<VirtualHost *:80>
    ServerName escola-demo.localhost
    DocumentRoot /caminho/para/edusaas/public

    <Directory /caminho/para/edusaas/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Crie o arquivo `.htaccess` em `public/`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

#### Nginx

```nginx
server {
    listen 80;
    server_name escola-demo.localhost;
    root /caminho/para/edusaas/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 8. Adicione ao hosts (para desenvolvimento local)

```bash
sudo nano /etc/hosts
```

Adicione:

```
127.0.0.1  escola-demo.localhost
```

### 9. Acesse o sistema

Abra o navegador em: **http://escola-demo.localhost**

**Credenciais padrão:**
- **E-mail:** professora@escola-demo.com
- **Senha:** Admin@123

---

## 🎨 Personalização por Escola

Cada escola (tenant) pode configurar:

- ✅ **Logo** (upload de imagem)
- ✅ **Cores primária e secundária** (injetadas como CSS vars)
- ✅ **Cor do texto** nos botões/destaque
- ✅ **Tema padrão** (claro ou escuro)
- ✅ **Nome da escola** (aparece no login e sidebar)

Acessível em: **Configurações > Visual**

---

## 🗂️ Estrutura de Pastas

```
edusaas/
├── app/
│   ├── Controllers/       # Controladores
│   ├── Models/            # Models com BaseModel (CRUD genérico)
│   ├── Services/          # Regras de negócio
│   ├── Middleware/        # Auth, Tenant, Role
│   ├── Helpers/           # Funções auxiliares
│   └── Views/             # Templates PHP
│       ├── layouts/       # main.php, auth.php
│       ├── auth/          # login, login-pais
│       └── dashboard/     # dashboards por papel
├── config/                # Configurações (database, app, roles)
├── database/
│   ├── migrations/        # SQL ordenado (001, 002...)
│   └── seeds/             # Dados de exemplo
├── public/                # DocumentRoot
│   ├── index.php          # Front controller
│   └── assets/
│       ├── css/           # base, components, layout, auth
│       ├── js/            # app.js
│       └── uploads/       # Logos das escolas
└── routes/
    └── web.php            # Todas as rotas
```

---

## 🔐 Papéis e Permissões

| Papel        | Descrição                                      |
|-------------|------------------------------------------------|
| super_admin | Gerencia todas as escolas (dono do SaaS)       |
| diretor     | Gerencia a escola, config visual/acadêmica     |
| coordenador | Abre recuperação, vê tudo, bloqueia períodos   |
| secretaria  | Cadastra alunos, turmas, responsáveis          |
| professor   | Lança notas, frequência, anotações             |
| pai         | Vê boletim, frequência, anotações do filho     |

Ver mapa completo em `config/roles.php`

---

## 📱 Mobile-First

Todo o CSS foi escrito pensando **primeiro no celular** (320px), depois ampliado com media queries.

- Sidebar vira hamburger menu
- Bottom navigation nativa
- Inputs grandes e espaçados para touch
- Lançamento de nota otimizado para tela pequena

Teste em: **iPhone SE, Galaxy S8, iPad Mini**

---

## 🧪 Desenvolvimento

### Rodando localmente (PHP built-in server)

```bash
cd public
php -S localhost:8000
```

Acesse: http://localhost:8000

**⚠️ Não use em produção!** Apenas para desenvolvimento.

---

## 🐛 Debug

Edite `.env`:

```env
APP_DEBUG=true
```

Isso mostra erros detalhados no navegador. **Desative em produção.**

---

## 📝 Licença

Código proprietário. Todos os direitos reservados.

---

## 👨‍💻 Autor

Desenvolvido como solução para escolas que precisam de um sistema moderno, mobile e fácil de usar.

---

## 📞 Suporte

Dúvidas ou problemas? Verifique:
1. Migrations executadas na ordem
2. Credenciais do `.env` corretas
3. Permissões da pasta `public/assets/uploads/` (775)
4. Logs do PHP/MySQL

---

**🚀 Boa sorte e bom desenvolvimento!**
