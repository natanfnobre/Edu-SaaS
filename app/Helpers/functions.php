<?php

/**
 * Funções globais auxiliares — carregadas no bootstrap.
 */

// ── Data / Hora ───────────────────────────────────────────────────

function now(): string
{
    return date('Y-m-d H:i:s');
}

function today(): string
{
    return date('Y-m-d');
}

function dateBr(string $date): string
{
    return date('d/m/Y', strtotime($date));
}

function datetimeBr(string $datetime): string
{
    return date('d/m/Y H:i', strtotime($datetime));
}

// ── String ────────────────────────────────────────────────────────

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slug(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = str_replace(
        ['á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç','ñ'],
        ['a','a','a','a','e','e','i','o','o','o','u','u','c','n'],
        $text
    );
    return preg_replace('/[^a-z0-9]+/', '-', $text);
}

function maskCpf(string $cpf): string
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

function initials(string $name): string
{
    $words = explode(' ', trim($name));
    $first = mb_substr($words[0], 0, 1);
    $last  = count($words) > 1 ? mb_substr(end($words), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

function truncate(string $text, int $length = 80): string
{
    return mb_strlen($text) > $length
        ? mb_substr($text, 0, $length) . '…'
        : $text;
}

// ── Auth ──────────────────────────────────────────────────────────

function auth(): \App\Services\AuthService
{
    static $instance = null;
    return $instance ??= new \App\Services\AuthService();
}

function currentUser(): ?array
{
    return auth()->user();
}

function can(string $permission): bool
{
    return auth()->can($permission);
}

function hasRole(string|array $roles): bool
{
    return auth()->hasRole($roles);
}

function tenantId(): ?int
{
    return auth()->tenantId();
}

// ── URL / Rotas ───────────────────────────────────────────────────

function url(string $path = ''): string
{
    $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
    return $base . '/' . ltrim($path, '/');
}

function route(string $name, array $params = []): string
{
    return \App\Helpers\Router::url($name, $params);
}

function redirect(string $url, int $code = 302): never
{
    http_response_code($code);
    header("Location: {$url}");
    exit;
}

// ── Flash ─────────────────────────────────────────────────────────

function flash(string $type, string $message): void
{
    \App\Helpers\Flash::set($type, $message);
}

// ── CSRF ──────────────────────────────────────────────────────────

function csrfField(): string
{
    return \App\Helpers\Csrf::field();
}

function csrfToken(): string
{
    return \App\Helpers\Csrf::token();
}

// ── Assets ────────────────────────────────────────────────────────

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function tenantLogo(): string
{
    $tenant = $_SESSION['tenant'] ?? null;
    if ($tenant && !empty($tenant['visual']['logo_path'])) {
        return asset('uploads/' . $tenant['visual']['logo_path']);
    }
    return asset('img/logo-default.svg');
}

// ── Nota / Média ──────────────────────────────────────────────────

function formatNota(mixed $nota): string
{
    if ($nota === null || $nota === '') return '--';
    return number_format((float) $nota, 1, ',', '');
}

function statusNota(float $nota, float $minima): string
{
    if ($nota >= $minima) return 'aprovado';
    if ($nota >= ($minima * 0.6)) return 'recuperacao';
    return 'reprovado';
}

function badgeStatus(string $status): string
{
    return match ($status) {
        'aprovado'    => '<span class="badge badge--success">Aprovado</span>',
        'recuperacao' => '<span class="badge badge--warning">Recuperação</span>',
        'reprovado'   => '<span class="badge badge--danger">Reprovado</span>',
        default       => '<span class="badge">--</span>',
    };
}

// ── View helpers ──────────────────────────────────────────────

function isActive(string ...$paths): string
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    foreach ($paths as $path) {
        if ($path === '/' && $current === '/') return 'active';
        if ($path !== '/' && str_starts_with($current, $path)) return 'active';
    }
    return '';
}

function roleName(string $role): string
{
    $roles = require CONFIG_PATH . '/roles.php';
    return $roles['roles'][$role] ?? $role;
}
