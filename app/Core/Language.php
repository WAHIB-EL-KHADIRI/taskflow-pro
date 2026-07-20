<?php

declare(strict_types=1);

namespace App\Core;

class Language
{
    private static ?Language $instance = null;
    private string $locale;
    private array $messages = [];

    private function __construct()
    {
        $session = \App\Http\Session::getInstance();
        $locale = $session->get('locale', $_ENV['APP_LOCALE'] ?? 'fr');

        $this->load($locale);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load(string $locale): void
    {
        $this->locale = $locale;
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $locale . '.php';

        if (file_exists($path)) {
            $this->messages = require $path;
        } else {
            \App\Http\Session::getInstance()->set('locale', 'fr');
            $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'fr.php';
            if (file_exists($path)) {
                $this->messages = require $path;
            }
        }
    }

    public function get(string $key, array $params = []): string
    {
        $keys = explode('.', $key);
        $value = $this->messages;

        foreach ($keys as $segment) {
            if (is_array($value) && isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $key;
            }
        }

        if (is_string($value)) {
            foreach ($params as $keyName => $valName) {
                $value = str_replace(":{$keyName}", $valName, $value);
            }
        }

        return is_string($value) ? $value : $key;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function direction(): string
    {
        return $this->locale === 'ar' ? 'rtl' : 'ltr';
    }

    public function switch(string $locale): void
    {
        \App\Http\Session::getInstance()->set('locale', $locale);
        $this->load($locale);
    }

    public static function trans(string $key, array $params = [], ?self $lang = null): string
    {
        $lang = $lang ?? self::getInstance();
        return $lang->get($key, $params);
    }

    public function all(): array
    {
        return $this->messages;
    }

    public static function supported(): array
    {
        return ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
    }

    public function setLocale(string $locale): void
    {
        $supported = self::supported();
        if (isset($supported[$locale])) {
            $this->locale = $locale;
            $this->load($locale);
        }
    }
}
