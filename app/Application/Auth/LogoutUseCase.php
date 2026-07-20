<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Http\Session;

class LogoutUseCase
{
    private Session $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    public function execute(): array
    {
        $this->session->destroy();

        return ['success' => true, 'message' => 'Déconnexion réussie.'];
    }
}
