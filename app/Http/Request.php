<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    private static ?Request $instance = null;
    private array $queryParams;
    private array $postParams;
    private array $cookies;
    private array $files;
    private array $server;
    private ?string $body = null;

    private function __construct()
    {
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->server = $_SERVER;
    }

    public static function capture(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getInstance(): self
    {
        return self::capture();
    }

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($this->postParams['_method'])) {
            return strtoupper($this->postParams['_method']);
        }
        return $method;
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH);
    }

    public function url(): string
    {
        return ($this->isSecure() ? 'https://' : 'http://') . $this->host() . $this->uri();
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || (int)($this->server['SERVER_PORT'] ?? 80) === 443;
    }

    public function host(): string
    {
        return $this->server['HTTP_HOST'] ?? 'localhost';
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['HTTP_CLIENT_IP']
            ?? $this->server['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return Sanitizer::input($this->queryParams[$key] ?? $default);
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return Sanitizer::input($this->postParams[$key] ?? $default);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post($key) ?? $this->query($key, $default);
    }

    public function all(): array
    {
        return array_merge(
            $this->queryParams,
            $this->postParams
        );
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }
        return $result;
    }

    public function has(string $key): bool
    {
        return isset($this->postParams[$key]) || isset($this->queryParams[$key]);
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK
            ? $this->files[$key]
            : null;
    }

    public function hasFile(string $key): bool
    {
        return $this->file($key) !== null;
    }

    public function body(): string
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://stdin');
        }
        return $this->body;
    }

    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function wantsJson(): bool
    {
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, '/json') || str_contains($accept, '+json');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function session(): Session
    {
        return Session::getInstance();
    }

    public static function redirect(string $path): never
    {
        header("Location: {$path}");
        exit();
    }

    public function referer(): string
    {
        return $this->server['HTTP_REFERER'] ?? '/';
    }

    public function json(): array
    {
        $data = json_decode($this->body(), true);
        return $data ?: [];
    }

    public function csrfToken(): string
    {
        return Session::getInstance()->csrfToken();
    }
}
