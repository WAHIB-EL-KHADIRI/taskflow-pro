<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\UserRepositoryInterface;
use App\Http\Session;

class LoginUseCase
{
    private UserRepositoryInterface $userRepository;
    private Session $session;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->session = Session::getInstance();
    }

    public function execute(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
        }

        $this->session->set('user_id', (int)$user['id']);
        $this->session->set('user_name', $user['name']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('user_theme', $user['theme'] ?? 'light');
        $this->session->set('locale', $user['locale'] ?? 'fr');

        $this->userRepository->updateLastLogin((int)$user['id']);

        return ['success' => true, 'message' => 'Connexion réussie.', 'user' => $user];
    }
}
