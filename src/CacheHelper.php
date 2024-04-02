<?php

namespace Pantono\Utilities;

class CacheHelper
{
    public static function cleanCacheKey(string $key): string
    {
        $key = str_replace('/', '-', $key);
        $key = str_replace('\\', '--', $key);
        $key = str_replace('{', '', $key);
        $key = str_replace('}', '', $key);
        return str_replace('@', '-at-', $key);
    }
}
