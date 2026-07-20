<?php

declare(strict_types=1);

namespace App\Http;

class Security
{
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function generateCsrf(): string
    {
        return Session::getInstance()->csrfToken();
    }

    public static function checkCsrf(string $token): bool
    {
        return Session::getInstance()->verifyCsrf($token);
    }

    public static function escape(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    public static function sanitizeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^\w\-]/u', '_', $name);
        $name = substr($name, 0, 128);
        $extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);
        return $name . '.' . strtolower($extension);
    }

    public static function generatePassword(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-={}[]|:;<>?';
        return substr(str_shuffle($chars), 0, $length);
    }

    public static function generateRememberToken(): string
    {
        return bin2hex(random_bytes(64));
    }

    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function rateLimit(string $key, int $maxAttempts = 10, int $window = 60): bool
    {
        $session = Session::getInstance();
        $now = time();
        $attempts = $session->get($key, []);

        $attempts = array_filter($attempts, fn($t) => $t > ($now - $window));
        $attempts[] = $now;

        $session->set($key, $attempts);

        return count($attempts) <= $maxAttempts;
    }

    public static function getUploadMaxFilesize(): int
    {
        $size = ini_get('upload_max_filesize');
        return self::parseBytes($size);
    }

    private static function parseBytes(string $size): int
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int)substr($size, 0, -1);
        return match ($unit) {
            'G' => $value * 1073741824,
            'M' => $value * 1048576,
            'K' => $value * 1024,
            default => (int)$size,
        };
    }
}
