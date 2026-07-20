<?php

declare(strict_types=1);

namespace App\Application\Settings;

use App\Domain\User\UserRepositoryInterface;
use App\Http\Session;

class UpdatePasswordUseCase
{
    private UserRepositoryInterface $userRepository;
    private Session $session;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->session = Session::getInstance();
    }

    public function execute(string $currentPassword, string $newPassword): array
    {
        $userId = $this->session->userId();
        if (!$userId) {
            return ['success' => false, 'message' => 'Non autorisé.'];
        }

        $user = $this->userRepository->findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Mot de passe actuel incorrect.'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.'];
        }

        $this->userRepository->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return ['success' => true, 'message' => 'Mot de passe mis à jour.'];
    }
}
