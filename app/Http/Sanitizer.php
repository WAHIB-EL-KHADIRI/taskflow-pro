<?php

declare(strict_types=1);

namespace App\Http;

class Sanitizer
{
    public static function input(mixed $data): mixed
    {
        if (is_string($data)) {
            $data = trim($data);
            $data = stripslashes($data);
            return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        }
        if (is_array($data)) {
            return array_map([self::class, 'input'], $data);
        }
        return $data;
    }

    public static function rawString(string $data): string
    {
        return trim(preg_replace('/[^\p{L}\p{N}\s\-_.@]/u', '', $data));
    }

    public static function email(string $email): string
    {
        return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
    }

    public static function url(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function int(mixed $value, int $default = 0): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }
        return $default;
    }

    public static function float(mixed $value, float $default = 0.0): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        return $default;
    }

    public static function bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int)$value > 0;
        }
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'on', 'true', 'yes']);
        }
        return false;
    }

    public static function stripTags(string $data): string
    {
        return strip_tags($data);
    }

    public static function slug(string $string): string
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[^\w\s\-]/u', '', $string);
        $string = preg_replace('/[\s\-]+/', '-', $string);
        return trim($string, '-');
    }

    public static function phone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', trim($phone));
    }
}
