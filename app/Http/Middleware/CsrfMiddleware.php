<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Session;
use App\Http\Response;

class CsrfMiddleware
{
    public static function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $session = Session::getInstance();
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!$session->verifyCsrf($token)) {
                Response::error('CSRF token mismatch.', 419);
            }
        }
    }
}
