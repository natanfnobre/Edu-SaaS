<?php

namespace App\Middleware;

use App\Services\TenantService;
use App\Helpers\View;

class TenantMiddleware
{
    public function handle(): void
    {
        $service = new TenantService();
        $tenant  = $service->resolve();

        if (!$tenant) {
            http_response_code(404);
            die('Escola não encontrada.');
        }

        // Disponibiliza globalmente via superglobal de sessão
        // e constante para a requisição atual
        $_SESSION['tenant_id'] = $tenant['id'];

        // Compartilha com as views
        \App\Helpers\View::share('tenant', $tenant);
        \App\Helpers\View::share('cssVars', $service->generateCssVars($tenant));
    }
}
