<?php

declare(strict_types=1);

namespace Pantono\Utilities;

class OutputMasker
{
    public static function maskPhoneNumber(string $number): string
    {
        if (strlen($number) > 3) {
            return substr($number, 0, 2) . str_repeat('*', strlen($number) - 5) . substr($number, -3);
        }

        return '';
    }

    public static function maskName(string $name): string
    {
        $names = explode(' ', $name);
        foreach ($names as &$name) {
            $showCharacters = 1;
            if (strlen($name) <= 3) {
                $name = str_repeat('*', strlen($name));
            } else {
                if (strlen($name) <= 10 && strlen($name) >= 7) {
                    $showCharacters = 2;
                } elseif (strlen($name) > 10) {
                    $showCharacters = 3;
                }
                $minus = '-' . $showCharacters;
                $name = substr($name, 0, $showCharacters) . str_repeat('*', strlen($name) - ($showCharacters * 2)) . substr($name, (int)$minus);
            }
        }

        return implode(' ', $names);
    }

    public static function maskEmail(string $email): string
    {
        if ($email === '') {
            return '';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE) !== $email) {
            return '';
        }
        [$name, $domain] = explode('@', $email, 2);

        return substr($name, 0, 1) . str_repeat('*', strlen($name) - 1) . '@' . $domain;
    }

    public static function maskDob(\DateTimeInterface|string $dob): string
    {
        if ($dob instanceof \DateTimeInterface) {
            return $dob->format('Y-m-**');
        }

        $dob = DateTimeParser::parseDate($dob);
        if (empty($dob)) {
            return '';
        }

        return $dob->format('Y-m-**');
    }
}
