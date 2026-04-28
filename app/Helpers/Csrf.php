<?php

namespace App\Helpers;

class Csrf
{
    private static string $tokenName = '_csrf_token';

    public static function generate(): string
    {
        if (empty($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$tokenName];
    }

    public static function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::$tokenName])) {
            return false;
        }
        return hash_equals($_SESSION[self::$tokenName], $token);
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . $token . '">';
    }

    public static function token(): string
    {
        return self::generate();
    }

    public static function regenerate(): void
    {
        $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
    }
}
