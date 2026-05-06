<?php

function env(string $key, $default = null)
{
    static $values = null;

    if ($values === null) {
        $values = [];

        $envPath = dirname(__DIR__) . '/.env';

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                $parts = explode('=', $line, 2);

                if (count($parts) === 2) {
                    $values[trim($parts[0])] = trim($parts[1]);
                }
            }
        }
    }

    return $values[$key] ?? $default;
}