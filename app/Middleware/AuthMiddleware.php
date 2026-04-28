<?php

namespace App\Middleware;

use App\Helpers\View;

class AuthMiddleware
{
    public function handle(): void
    {
        if (empty($_SESSION['user_id']) && empty($_SESSION['pai_id'])) {
            View::redirect('/login');
        }
    }
}
