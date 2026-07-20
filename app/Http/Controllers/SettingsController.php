<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class SettingsController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('settings.index');
    }

    public function updateProfile(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function updatePassword(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function updateTheme(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function updateLocale(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }
}
