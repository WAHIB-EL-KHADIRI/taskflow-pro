<?php

declare(strict_types=1);

namespace App\Bootstrap;

class App
{
    private static ?App $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();

        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

        if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true') {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        set_exception_handler(function (\Throwable $e) {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            $message = $debug
                ? get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
                : 'Erreur interne du serveur.';

            http_response_code(500);
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $message]);
            } else {
                echo '<h1>500 - Erreur Interne</h1><pre>' . htmlspecialchars($message) . '</pre>';
            }
            exit();
        });

        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
