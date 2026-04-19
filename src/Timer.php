<?php

namespace Pantono\Utilities;

use Symfony\Component\Clock\MonotonicClock;
use Symfony\Component\Clock\DatePoint;

class Timer
{
    /**
     * @var array<string, array{'start': DatePoint, 'end': DatePoint|null}>
     */
    public static array $timers = [];

    /**
     * @return array{start:DatePoint, end: DatePoint|null}|null
     */
    public static function getTimer(string $name): ?array
    {
        return self::$timers[$name] ?? null;
    }


    public static function start(string $name): void
    {
        self::$timers[$name] = [
            'start' => (new MonotonicClock())->now(),
            'end' => null
        ];
    }

    public static function end(string $name): ?float
    {
        if (!isset(self::$timers[$name])) {
            self::start($name);
        }

        self::$timers[$name]['end'] = (new MonotonicClock())->now();
        return self::getTime($name);
    }

    public static function getTime(string $name): ?float
    {
        $timer = self::getTimer($name);
        if ($timer === null) {
            return null;
        }
        $end = $timer['end'] ?? (new MonotonicClock())->now();
        $time = 0;
        $diff = $end->diff($timer['start']);
        $time += $diff->h * 3600;
        $time += $diff->m * 60;
        $time += $diff->s;
        $time += $diff->f;
        return $time;
    }

    /**
     * @return array<int, array{name: string, time: float|null}>
     */
    public static function getAllTimesSorted(): array
    {
        $timers = [];
        foreach (self::$timers as $name => $timer) {
            $timers[] = ['name' => $name, 'time' => self::getTime($name)];
        }
        usort($timers, function ($a, $b) {
            return $a['time'] <=> $b['time'];
        });
        return $timers;
    }

    /**
     * @return array<string, float>
     */
    public static function listTimersMillisecond(): array
    {
        $timers = [];
        foreach (self::$timers as $name => $timer) {
            $timers[] = ['name' => $name, 'time' => self::getTime($name)];
        }
        usort($timers, function ($a, $b) {
            return $a['time'] <=> $b['time'];
        });
        $msTimers = [];
        foreach ($timers as $timer) {
            $msTimers[$timer['name']] = $timer['time'] * 1000;
        }
        return $msTimers;
    }
}
