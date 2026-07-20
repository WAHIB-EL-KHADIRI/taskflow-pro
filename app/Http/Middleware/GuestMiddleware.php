<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Response;
use App\Http\Session;

class GuestMiddleware
{
    public static function handle(): void
    {
        $session = Session::getInstance();
        if ($session->isAuthenticated()) {
            Response::redirect('/dashboard');
        }
    }
}
