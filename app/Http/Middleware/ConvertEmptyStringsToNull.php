<?php

declare(strict_types=1);

namespace App\Http\Middleware;

class ConvertEmptyStringsToNull
{
    public static function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            array_walk_recursive($_POST, function (&$value) {
                if (is_string($value) && $value === '') {
                    $value = null;
                }
            });
        }
    }
}
