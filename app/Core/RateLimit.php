<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple session-based rate limiter.
 *
 * Tracks the number of attempts under a given key within a sliding window.
 */
final class RateLimit
{
    public static function tooManyAttempts(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
    {
        self::prune($key, $windowSeconds);
        $attempts = (int) ($_SESSION['_rl'][$key]['count'] ?? 0);
        return $attempts >= $maxAttempts;
    }

    public static function increment(string $key): void
    {
        if (!isset($_SESSION['_rl'][$key])) {
            $_SESSION['_rl'][$key] = ['count' => 0, 'timestamps' => []];
        }
        $_SESSION['_rl'][$key]['count']++;
        $_SESSION['_rl'][$key]['timestamps'][] = time();
    }

    public static function reset(string $key): void
    {
        unset($_SESSION['_rl'][$key]);
    }

    private static function prune(string $key, int $windowSeconds): void
    {
        if (!isset($_SESSION['_rl'][$key]['timestamps'])) {
            return;
        }
        $cutoff = time() - $windowSeconds;
        $timestamps = array_filter(
            $_SESSION['_rl'][$key]['timestamps'],
            static fn(int $t): bool => $t > $cutoff
        );
        $_SESSION['_rl'][$key]['timestamps'] = array_values($timestamps);
        $_SESSION['_rl'][$key]['count']      = count($timestamps);
    }
}