<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Session;

class AuthMiddleware
{
    public static function handle(): void
    {
        $session = Session::getInstance();
        if (!$session->isAuthenticated()) {
            Request::redirect('/login');
        }
    }
}
