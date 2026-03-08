<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env file loader.
 * Reads KEY=VALUE pairs from a file and exposes them via getenv() / $_ENV.
 * Lines starting with # are ignored. Already-set variables are not overwritten.
 */
final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and lines without '='
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Determine whether the value is surrounded by matching quotes
            $isQuoted = strlen($value) >= 2
                && $value[0] === $value[strlen($value) - 1]
                && in_array($value[0], ['"', "'"], true);

            // Strip inline comments only for unquoted values (e.g.  foo # comment → foo)
            if (!$isQuoted) {
                $commentPos = strpos($value, ' #');
                if ($commentPos !== false) {
                    $value = trim(substr($value, 0, $commentPos));
                }
            }

            // Strip surrounding quotes from quoted values
            if ($isQuoted) {
                $value = substr($value, 1, -1);
            }

            // Never overwrite variables that were already set in the environment
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
