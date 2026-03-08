<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** Render a hidden input field with the CSRF token. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /** Validate the token submitted via POST. */
    public static function verify(): bool
    {
        $submitted = (string) ($_POST['_csrf_token'] ?? '');
        $stored    = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        return $stored !== '' && hash_equals($stored, $submitted);
    }
}
