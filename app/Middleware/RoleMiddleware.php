<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Helpers\View;
use App\Helpers\Flash;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(): void
    {
        if (empty($this->allowedRoles)) return;

        $auth = new AuthService();
        if (!$auth->hasRole($this->allowedRoles)) {
            Flash::error('Você não tem permissão para acessar esta área.');
            View::redirect('/dashboard');
        }
    }
}
