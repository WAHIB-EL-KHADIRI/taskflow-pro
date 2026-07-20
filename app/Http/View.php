<?php

declare(strict_types=1);

namespace App\Http;

class View
{
    private static ?View $instance = null;
    private static array $shared = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewPath = self::viewPath($view);

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        $content = self::renderFile($viewPath, array_merge(self::$shared, $data));

        if ($layout !== false && file_exists(self::viewPath("layouts.{$layout}"))) {
            $content = self::renderFile(
                self::viewPath("layouts.{$layout}"),
                array_merge(self::$shared, $data, ['slot' => $content])
            );
        }

        echo $content;
    }

    public static function renderRaw(string $view, array $data = []): string
    {
        return self::renderFile(self::viewPath($view), array_merge(self::$shared, $data));
    }

    private static function viewPath(string $view): string
    {
        $parts = explode('.', str_replace('/', '.', $view));
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        foreach ($parts as $part) {
            $path .= DIRECTORY_SEPARATOR . $part;
        }
        return $path . '.php';
    }

    private static function renderFile(string $__path, array $__data): string
    {
        extract($__data, EXTR_OVERWRITE);
        ob_start();
        include $__path;
        return ob_get_clean() ?: '';
    }
}
