<?php

namespace App\Helpers;

class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function warning(string $message): void
    {
        self::set('warning', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    public static function get(string $type): array
    {
        $messages = $_SESSION['_flash'][$type] ?? [];
        unset($_SESSION['_flash'][$type]);
        return $messages;
    }

    public static function all(): array
    {
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }

    public static function has(string $type): bool
    {
        return !empty($_SESSION['_flash'][$type]);
    }

    public static function hasAny(): bool
    {
        return !empty($_SESSION['_flash']);
    }
}
