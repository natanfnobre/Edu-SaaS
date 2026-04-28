<?php

namespace App\Controllers;

use App\Helpers\{View, Flash, Csrf, Validator};
use App\Services\AuthService;
use App\Models\User;

class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    // ── Login usuário ─────────────────────────────────────────────

    public function showLogin(): void
    {
        if ($this->auth->check()) {
            redirect('/dashboard');
        }
        View::render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        // Valida CSRF
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida. Tente novamente.');
            redirect('/login');
        }

        $v = Validator::make($_POST, [
            'email' => 'required|email',
            'senha' => 'required|min:6',
        ]);

        if ($v->fails()) {
            Flash::error('Preencha e-mail e senha corretamente.');
            redirect('/login');
        }

        $tenantId = (int) ($_SESSION['tenant_id'] ?? 0);

        if (!$tenantId) {
            Flash::error('Escola não identificada.');
            redirect('/login');
        }

        if ($this->auth->attempt($_POST['email'], $_POST['senha'], $tenantId)) {
            $user = $this->auth->user();

            // Redireciona por papel
            redirect($this->redirectByRole($user['papel']));
        }

        Flash::error('E-mail ou senha incorretos.');
        redirect('/login');
    }

    public function logout(): void
    {
        $this->auth->logout();
        Flash::info('Você saiu do sistema.');
        redirect('/login');
    }

    // ── Login pais ────────────────────────────────────────────────

    public function showLoginPais(): void
    {
        if (!empty($_SESSION['pai_id'])) {
            redirect('/pais');
        }
        View::render('auth/login-pais', [], 'auth');
    }

    public function loginPais(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/pais/login');
        }

        $tenantId = (int) ($_SESSION['tenant_id'] ?? 0);

        $identificador = trim($_POST['identificador'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if ($identificador === '' || $senha === '') {
            Flash::error('Preencha CPF ou e-mail e a senha.');
            redirect('/pais/login');
        }

        // Tenta autenticar como responsável
        if ($this->auth->attemptPai($identificador, $senha, $tenantId)) {
            redirect('/pais');
        }

        Flash::error('Identificador ou senha incorretos.');
        redirect('/pais/login');
    }

    public function logoutPais(): void
    {
        $this->auth->logout();
        redirect('/pais/login');
    }

    // ── Perfil ────────────────────────────────────────────────────

    public function perfil(): void
    {
        View::render('auth/perfil', [
            'user' => currentUser(),
        ]);
    }

    public function updatePerfil(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            Flash::error('Requisição inválida.');
            redirect('/perfil');
        }

        $v = Validator::make($_POST, [
            'nome'     => 'required|min:3|max:150',
            'email'    => 'required|email',
            'telefone' => 'max:20',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/perfil');
        }

        $userModel = new User();
        $userModel->update((int) $_SESSION['user_id'], [
            'nome'      => $v->sanitized()['nome'],
            'email'     => $v->sanitized()['email'],
            'telefone'  => $v->sanitized()['telefone'] ?? null,
        ], tenantId());

        Flash::success('Perfil atualizado com sucesso!');
        redirect('/perfil');
    }

    public function updateSenha(): void
    {
        if (!Csrf::validate($_POST['_csrf_token'] ?? '')) {
            redirect('/perfil');
        }

        $v = Validator::make($_POST, [
            'senha_atual'           => 'required',
            'nova_senha'            => 'required|min:8',
            'nova_senha_confirmacao'=> 'required|confirmed:nova_senha',
        ]);

        if ($v->fails()) {
            Flash::error(implode(' ', array_merge(...array_values($v->errors()))));
            redirect('/perfil');
        }

        $user = currentUser();
        if (!password_verify($_POST['senha_atual'], $user['senha_hash'])) {
            Flash::error('Senha atual incorreta.');
            redirect('/perfil');
        }

        $userModel = new User();
        $userModel->updatePassword((int) $_SESSION['user_id'], $_POST['nova_senha']);

        Flash::success('Senha alterada com sucesso!');
        redirect('/perfil');
    }

    public function updateTema(): void
    {
        $tema = $_POST['tema'] ?? 'sistema';
        if (!in_array($tema, ['claro', 'escuro', 'sistema'])) $tema = 'sistema';

        $userModel = new User();
        $userModel->updateTheme((int) $_SESSION['user_id'], $tema);
        $_SESSION['tema'] = $tema;

        View::json(['ok' => true, 'tema' => $tema]);
    }

    // ── Privado ───────────────────────────────────────────────────

    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'super_admin'             => '/dashboard',
            'diretor', 'coordenador' => '/dashboard',
            'secretaria'             => '/alunos',
            'professor'              => '/dashboard',
            default                  => '/dashboard',
        };
    }
}
