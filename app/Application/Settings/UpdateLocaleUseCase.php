<?php

declare(strict_types=1);

namespace App\Application\Settings;

use App\Domain\User\UserRepositoryInterface;
use App\Http\Session;

class UpdateLocaleUseCase
{
    private UserRepositoryInterface $userRepository;
    private Session $session;

    private const VALID_LOCALES = ['fr', 'en', 'ar', 'es', 'de'];

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->session = Session::getInstance();
    }

    public function execute(string $locale): array
    {
        $userId = $this->session->userId();
        if (!$userId) {
            return ['success' => false, 'message' => 'Non autorisé.'];
        }

        if (!in_array($locale, self::VALID_LOCALES, true)) {
            return ['success' => false, 'message' => 'Langue invalide.'];
        }

        $this->userRepository->update($userId, ['locale' => $locale]);
        $this->session->set('locale', $locale);

        return ['success' => true, 'message' => 'Langue mise à jour.'];
    }
}
