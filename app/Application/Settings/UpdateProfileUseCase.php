<?php

declare(strict_types=1);

namespace App\Application\Settings;

use App\Domain\User\UserRepositoryInterface;
use App\Http\Session;

class UpdateProfileUseCase
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
        $userId = $this->session->userId();
        if (!$userId) {
            return ['success' => false, 'message' => 'Non autorisé.'];
        }

        $this->userRepository->update($userId, [
            'name' => $data['name'],
            'bio' => $data['bio'] ?? null,
        ]);

        $this->session->set('user_name', $data['name']);

        return ['success' => true, 'message' => 'Profil mis à jour.'];
    }
}
