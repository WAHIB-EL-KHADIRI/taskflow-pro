<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\LoginUseCase;
use App\Application\Auth\RegisterUseCase;
use App\Infrastructure\Persistence\UserRepository\UserRepository;

class AuthController extends AbstractController
{
    private const MIN_PASSWORD_LENGTH = 8;

    public function loginForm(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth.login');
    }

    public function login(): void
    {
        $email = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        $result = (new LoginUseCase(new UserRepository()))->execute($email, $password);

        if ($result['success'] !== true) {
            // The use case deliberately returns one message for "no such
            // account" and for "wrong password". Keep that here: two distinct
            // messages would turn the login form into an account oracle.
            $this->session->flash('error', (string) $result['message']);
            $this->session->flash('old_email', $email);
            $this->back();
        }

        // The use case has just written the identity into whatever session id
        // the browser arrived with. Issuing a new id now is what stops someone
        // who planted that id beforehand from inheriting the logged-in session.
        $this->session->regenerate();
        $this->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth.register');
    }

    public function register(): void
    {
        $name = trim((string) $this->input('name', ''));
        $email = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');
        $confirmation = (string) $this->input('password_confirmation', '');

        // RegisterUseCase validates nothing -- it checks the address is unused
        // and then writes. Everything below has to happen before it is called,
        // or an empty password becomes a real account.
        $error = $this->validateRegistration($name, $email, $password, $confirmation);

        if ($error !== null) {
            $this->session->flash('error', $error);
            $this->session->flash('old_name', $name);
            $this->session->flash('old_email', $email);
            $this->back();
        }

        $result = (new RegisterUseCase(new UserRepository()))->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        if ($result['success'] !== true) {
            $this->session->flash('error', (string) $result['message']);
            $this->session->flash('old_name', $name);
            $this->session->flash('old_email', $email);
            $this->back();
        }

        $this->session->regenerate();
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        // Clear first, then regenerate: the new id starts empty, so a copy of
        // the old cookie cannot be replayed against the old contents.
        $this->session->clear();
        $this->session->regenerate();
        $this->redirect('/login');
    }

    private function validateRegistration(
        string $name,
        string $email,
        string $password,
        string $confirmation
    ): ?string {
        if ($name === '') {
            return 'Le nom est obligatoire.';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return 'Adresse email invalide.';
        }

        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return sprintf(
                'Le mot de passe doit contenir au moins %d caracteres.',
                self::MIN_PASSWORD_LENGTH
            );
        }

        if (!hash_equals($password, $confirmation)) {
            return 'Les mots de passe ne correspondent pas.';
        }

        return null;
    }
}
