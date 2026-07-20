<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class AuthController extends AbstractController
{
    public function loginForm(): void
    {
        $this->view('auth.login');
    }

    public function login(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function registerForm(): void
    {
        $this->view('auth.register');
    }

    public function register(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function logout(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }
}
