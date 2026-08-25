<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Settings\UpdateLocaleUseCase;
use App\Application\Settings\UpdatePasswordUseCase;
use App\Application\Settings\UpdateProfileUseCase;
use App\Application\Settings\UpdateThemeUseCase;
use App\Infrastructure\Persistence\UserRepository\UserRepository;

class SettingsController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();

        $user = (new UserRepository())->findById((int) $this->userId());

        $this->view('settings.index', [
            'user' => $user ?? [],
            'title' => 'Parametres',
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth();

        $name = trim((string) $this->input('name', ''));

        // UpdateProfileUseCase reads $data['name'] without a guard, so an
        // absent or blank field would either throw or blank the display name.
        if ($name === '') {
            $this->fail('Le nom est obligatoire.');
        }

        $this->finish((new UpdateProfileUseCase(new UserRepository()))->execute([
            'name' => $name,
            'bio' => trim((string) $this->input('bio', '')),
        ]));
    }

    public function updatePassword(): void
    {
        $this->requireAuth();

        $current = (string) $this->input('current_password', '');
        $new = (string) $this->input('new_password', '');
        $confirmation = (string) $this->input('new_password_confirmation', '');

        // The use case verifies the current password and the 8-character
        // minimum. It has no notion of a confirmation field, so the match is
        // checked here, before anything is written.
        if (!hash_equals($new, $confirmation)) {
            $this->fail('Les mots de passe ne correspondent pas.');
        }

        $this->finish((new UpdatePasswordUseCase(new UserRepository()))->execute($current, $new));
    }

    public function updateTheme(): void
    {
        $this->requireAuth();

        $this->finish(
            (new UpdateThemeUseCase(new UserRepository()))->execute((string) $this->input('theme', ''))
        );
    }

    public function updateLocale(): void
    {
        $this->requireAuth();

        $this->finish(
            (new UpdateLocaleUseCase(new UserRepository()))->execute((string) $this->input('locale', ''))
        );
    }

    /**
     * @param array<string, mixed> $result
     */
    private function finish(array $result): never
    {
        $key = ($result['success'] ?? false) === true ? 'success' : 'error';
        $this->session->flash($key, (string) ($result['message'] ?? ''));
        $this->back();
    }

    private function fail(string $message): never
    {
        $this->session->flash('error', $message);
        $this->back();
    }
}
