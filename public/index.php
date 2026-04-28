<?php

declare(strict_types=1);

// ── Caminhos base ────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH',   APP_PATH  . '/Views');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ── Autoload ─────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    // Converte namespace em path: App\Controllers\AuthController → app/Controllers/AuthController.php
    $path = ROOT_PATH . '/' . str_replace(['App\\', '\\'], ['app/', '/'], $class) . '.php';
    if (file_exists($path)) require_once $path;
});

// ── Funções globais ───────────────────────────────────────────────
require_once APP_PATH . '/Helpers/functions.php';

// ── Variáveis de ambiente (.env simples) ──────────────────────────
if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

// ── Configuração da aplicação ─────────────────────────────────────
$config = require CONFIG_PATH . '/app.php';
date_default_timezone_set($config['timezone']);
ini_set('display_errors', $config['debug'] ? '1' : '0');
error_reporting($config['debug'] ? E_ALL : 0);

// ── Sessão segura ─────────────────────────────────────────────────
session_name($config['session']['name']);
session_set_cookie_params([
    'lifetime' => $config['session']['lifetime'],
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ── Headers de segurança ──────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Rotas ─────────────────────────────────────────────────────────
require_once ROOT_PATH . '/routes/web.php';

// ── Dispatch ──────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

\App\Helpers\Router::dispatch($method, $uri);
