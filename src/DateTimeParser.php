<?php

declare(strict_types=1);

namespace Pantono\Utilities;

use DateTime;
use DateTimeImmutable;

class DateTimeParser
{
    const DATE_FORMAT = 'Y-m-d';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public static function parseDate(string $date): ?DateTime
    {
        if (empty($date)) {
            return null;
        }
        $hasTime = true;
        if (str_contains($date, ' ')) {
            $formats = [
                'Y-m-d H:i:s',
                'd/m/Y H:i:s',
                'M j Y H:i:s:A'
            ];
        } else {
            $hasTime = false;
            $formats = [
                'Y-m-d',
                'd/m/Y',
                'H:i:s',
                'M j Y'
            ];
        }

        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $date);
            if ($dateTime instanceof DateTime) {
                if ($hasTime === false) {
                    $dateTime->setTime(0, 0, 0);
                }
                return $dateTime;
            }
        }

        try {
            return new DateTime($date);
        } catch (\Exception $e) {
        }

        return null;
    }

    public static function parseDateWithFormat(string $date, string $format): ?string
    {
        $date = trim($date);
        $date = self::parseDate($date);
        return $date?->format($format);
    }

    /**
     * Parse date
     *
     * @param string $date Date
     *
     * @return DateTimeImmutable|null
     */
    public static function parseDateImmutable(string $date): ?DateTimeImmutable
    {
        if (empty($date)) {
            return null;
        }
        if (str_contains($date, ' ')) {
            //Has Time
            $formats = [
                'Y-m-d H:i:s',
                'd/m/Y H:i:s'
            ];
        } else {
            //Does not have time
            $formats = [
                'Y-m-d',
                'd/m/Y'
            ];
        }

        foreach ($formats as $format) {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $date);
            if ($dateTime instanceof \DateTimeImmutable) {
                return $dateTime;
            }
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception $e) {
        }

        return null;
    }

    public static function diffInMinutes(\DateTimeInterface $start, \DateTimeInterface $end): ?int
    {
        $diff = $start->diff($end);
        $minutes = $diff->m;
        if ($diff->h > 1) {
            $minutes += ($diff->h * 60);
        }
        if ($diff->d > 1) {
            $minutes += ($diff->d * 1440);
        }

        return $minutes;
    }

    public static function roundTime(\DateTime $datetime, int $precision = 15): void
    {
        $second = (int)$datetime->format("s");
        if ($second > 30) {
            $datetime->add(new \DateInterval("PT" . (60 - $second) . "S"));
        } elseif ($second > 0) {
            $datetime->sub(new \DateInterval("PT" . $second . "S"));
        }
        $minute = (int)$datetime->format("i");
        $minute = $minute % $precision;
        if ($minute > 0) {
            $diff = $precision - $minute;
            $datetime->add(new \DateInterval("PT" . $diff . "M"));
        }
    }
}
