<?php

declare(strict_types=1);

namespace App\Application\Settings;

use App\Domain\User\UserRepositoryInterface;
use App\Http\Session;

class UpdateThemeUseCase
{
    private UserRepositoryInterface $userRepository;
    private Session $session;

    private const VALID_THEMES = ['light', 'dark'];

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->session = Session::getInstance();
    }

    public function execute(string $theme): array
    {
        $userId = $this->session->userId();
        if (!$userId) {
            return ['success' => false, 'message' => 'Non autorisé.'];
        }

        if (!in_array($theme, self::VALID_THEMES, true)) {
            return ['success' => false, 'message' => 'Thème invalide.'];
        }

        $this->userRepository->update($userId, ['theme' => $theme]);
        $this->session->set('user_theme', $theme);

        return ['success' => true, 'message' => 'Thème mis à jour.'];
    }
}
