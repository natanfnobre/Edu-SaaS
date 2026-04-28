<?php

return [
    'name'      => 'EduSaaS',
    'version'   => '1.0.0',
    'debug'     => $_ENV['APP_DEBUG'] ?? false,
    'timezone'  => 'America/Sao_Paulo',
    'locale'    => 'pt_BR',
    'url'       => $_ENV['APP_URL'] ?? 'http://localhost',

    // Sessão
    'session' => [
        'name'     => 'edusaas_sess',
        'lifetime' => 7200, // 2 horas
    ],

    // Upload
    'upload' => [
        'max_size'       => 5 * 1024 * 1024, // 5MB
        'allowed_images' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_docs'   => ['application/pdf', 'image/jpeg', 'image/png'],
        'path'           => __DIR__ . '/../public/assets/uploads/',
    ],

    // Segurança
    'bcrypt_cost' => 12,
    'csrf_token_name' => '_csrf_token',
];
