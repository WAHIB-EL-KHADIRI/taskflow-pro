<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\UserRepositoryInterface;
use App\Http\Session;

class RegisterUseCase
{
    private UserRepositoryInterface $userRepository;
    private Session $session;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->session = Session::getInstance();
    }

    public function execute(array $data): array
    {
        $existing = $this->userRepository->findByEmail($data['email']);
        if ($existing) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
        }

        $userId = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'locale' => $data['locale'] ?? 'fr',
            'theme' => $data['theme'] ?? 'light',
        ]);

        $this->session->set('user_id', $userId);
        $this->session->set('user_name', $data['name']);
        $this->session->set('user_role', 'user');
        $this->session->set('user_theme', 'light');
        $this->session->set('locale', 'fr');

        return ['success' => true, 'message' => 'Compte créé avec succès.'];
    }
}
