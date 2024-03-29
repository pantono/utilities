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

        return strval(time());
    }

    public static function getEnv(): string
    {
        if (defined('APPLICATION_ENV')) {
            return constant('APPLICATION_ENV');
        }

        throw new \RuntimeException('APPLICATION_ENV not set');
    }
}
