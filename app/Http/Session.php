<?php

declare(strict_types=1);

namespace App\Http;

class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = ($_ENV['SESSION_SECURE'] ?? 'false') === 'true';
            $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 120) * 60;

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_name('TFPSESSID');
            session_start();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        session_unset();
    }

    public function destroy(): void
    {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], true);
        session_destroy();
    }

    public function flash(string $key, ?string $value = null): ?string
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        if ($val !== null) {
            unset($_SESSION['_flash'][$key]);
        }
        return $val;
    }

    public function flashHas(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_expires'] = time() + 3600;
        }
        if (($_SESSION['csrf_token_expires'] ?? 0) < time()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_expires'] = time() + 3600;
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCsrf(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function regenerate(): void
    {
        $userId = $this->get('user_id');
        $userName = $this->get('user_name');
        $userLocale = $this->get('user_locale');
        $sessionData = $_SESSION;
        session_regenerate_id(true);
        $_SESSION = $sessionData;
    }

    public function isAuthenticated(): bool
    {
        return $this->get('user_id') !== null;
    }

    public function userId(): ?int
    {
        return $this->get('user_id');
    }
}
