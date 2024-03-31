<?php

declare(strict_types=1);

namespace Pantono\Utilities;

class EphemeralCacheHelper
{
    /**
     * @var array<string, mixed>
     */
    public static array $cache = [];

    public static function getItem(string $key, callable $data): mixed
    {
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = $data();
        }

        return self::$cache[$key];
    }

    public static function setItem(string $key, mixed $value): void
    {
        self::$cache[$key] = $value;
    }

    public static function clearItem(string $key): void
    {
        if (isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }
    }

    public static function clearAllCache(): void
    {
        self::$cache = [];
    }
}
