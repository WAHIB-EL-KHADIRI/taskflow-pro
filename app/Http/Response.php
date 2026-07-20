<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    public static function success(mixed $data = null, string $message = 'Succes', int $status = 200): void
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    public static function error(string $message = 'Erreur', int $status = 400, ?array $errors = null): void
    {
        self::json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    public static function notFound(string $message = 'Introuvable'): void
    {
        self::error($message, 404);
    }

    public static function forbidden(string $message = 'Acces refuse'): void
    {
        self::error($message, 403);
    }

    public static function unauthorized(string $message = 'Non authentifie'): void
    {
        self::error($message, 401);
    }

    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header("Location: {$url}");
        exit();
    }

    public static function back(): never
    {
        $referer = Request::capture()->referer();
        self::redirect($referer ?: '/');
    }

    public static function download(string $path, string $filename = ''): never
    {
        $fullPath = dirname(__DIR__, 2) . $path;
        if (!file_exists($fullPath)) {
            self::notFound('Fichier introuvable');
        }

        $filename = $filename ?: basename($path);
        $filesize = filesize($fullPath);
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: no-cache');
        readfile($fullPath);
        exit();
    }
}
