<?php

namespace Pantono\Utilities;

class ApplicationHelper
{
    public static function getApplicationRoot(): string
    {
        if (defined('APPLICATION_PATH')) {
            return constant('APPLICATION_PATH');
        }
        throw new \RuntimeException('APPLICATION_PATH not set');
    }

    public static function getReleaseTimestamp(): string
    {
        if (defined('RELEASE_TIME')) {
            return constant('RELEASE_TIME');
        }

        define('RELEASE_TIME', strval(time()));
        return constant('RELEASE_TIME');
    }

    public static function getEnv(): string
    {
        if (defined('APPLICATION_ENV')) {
            return constant('APPLICATION_ENV');
        }

        throw new \RuntimeException('APPLICATION_ENV not set');
    }

    public static function appendTablePrefix(string $input): string
    {
        $prefix = isset($_ENV['TABLE_PREFIX']) ? $_ENV['TABLE_PREFIX'] . '_' : '';
        return $prefix . $input;
    }

    public static function interpolateEnv(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(function ($v) {
                return self::interpolateEnv($v);
            }, $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback('/\$\{([A-Za-z_][A-Za-z0-9_]*)(?::([^}]*))?}/', function ($matches) {
            $var = $matches[1];
            $default = $matches[2] ?? null;

            if (array_key_exists($var, $_ENV)) {
                return (string)$_ENV[$var];
            }

            if ($default !== null) {
                return $default;
            }

            return $matches[0];
        }, $value);
    }
}
