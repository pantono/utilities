<?php

declare(strict_types=1);

namespace Pantono\Utilities;

class StringUtilities
{
    public static function generateRandomToken(int $randomBytes = 100): string
    {
        return base64_encode(openssl_random_pseudo_bytes($randomBytes));
    }

    public static function snakeCase(string $string): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * @param string $str
     * @param array<string> $noStrip
     * @return string
     */
    public static function camelCase(string $str, array $noStrip = []): string
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        if ($str === null) {
            $str = '';
        }
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);

        return ucfirst($str);
    }

    public static function camelCaseToWords(string $string): string
    {
        $pattern = '/(?<!^)(?=[A-Z])/';
        $words = preg_split($pattern, $string);
        if ($words === false) {
            return $string;
        }
        return implode(' ', array_map('ucfirst', array_map('strtolower', $words)));
    }


    public static function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return self::maskString($email);
        }
        $parts = explode('@', $email, 2);
        $resultParts = [];
        foreach ($parts as $part) {
            $resultParts[] = self::maskString($part);
        }

        return implode('@', $resultParts);
    }

    public static function maskString(string $string): string
    {
        if (strlen($string) < 3) {
            return '**';
        }
        $start = 2;
        $end = 0;
        if (strlen($string) > 6) {
            $end = 2;
        }
        $mid = strlen($string) - $start - $end;

        return substr($string, 0, $start) . str_repeat('*', $mid) . ($end > 0 ? substr($string, (int)('-' . $end)) : '');
    }

    public static function generateRandomString(int $length = 10): string
    {
        $parts = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $parts[random_int(0, count($parts) - 1)];
        }
        return $string;
    }

    public static function generateRandomNumberString(int $length = 10): string
    {
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= random_int(0, 9);
        }

        return $string;
    }


    public static function boolValue(mixed $input): bool
    {
        if (is_bool($input)) {
            return $input;
        }

        if (is_string($input)) {
            $value = strtolower(trim($input));
            return match ($value) {
                '1', 'true', 'on', 'yes', 'y' => true,
                '0', 'false', 'off', 'no', 'n' => false,
                default => false,
            };
        }

        if (is_int($input)) {
            return $input === 1;
        }
        if (is_float($input)) {
            return (int)$input === 1;
        }

        return false;
    }
}
