<?php

declare(strict_types=1);

namespace App\Http;

class FileUpload
{
    private static array $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'zip', 'txt', 'csv'];
    private static int $maxSize = 10485760; // 10MB

    public static function upload(array $file, string $directory = 'uploads'): ?string
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, self::$allowedExtensions, true)) {
            throw new \RuntimeException("Extension de fichier non autoris\u00e9e: {$extension}");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!$mime || str_starts_with($mime, 'text/php')) {
            throw new \RuntimeException("Type MIME invalide");
        }

        if ($file['size'] > self::$maxSize) {
            throw new \RuntimeException("Fichier trop volumineux (max 10MB)");
        }

        $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $directory;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = uniqid('file_', true) . '.' . $extension;
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException("\u00c9chec de l'upload du fichier");
        }

        return 'assets/' . $directory . '/' . $safeName;
    }

    public static function delete(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $fullPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $path;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public static function setAllowedExtensions(array $extensions): void
    {
        self::$allowedExtensions = array_map('strtolower', $extensions);
    }

    public static function setMaxSize(int $bytes): void
    {
        self::$maxSize = $bytes;
    }
}
