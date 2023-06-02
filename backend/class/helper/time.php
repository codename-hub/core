<?php

namespace codename\core\helper;

use codename\core\app;
use codename\core\exception;
use codename\core\helper;
use ReflectionException;

/**
 * helper class for time purposes.
 * @package codename\core
 * @since 2017-05-10
 */
class time extends helper
{
    /**
     * returns an array of hh:mm elements (strings)
     * between start and end. MUST be intraday
     * @param string $start
     * @param string $end
     * @param int $stepMinutes
     * @param bool $showSeconds
     * @return string[]
     * @throws ReflectionException
     * @throws exception
     */
    public static function getTimeArray(string $start, string $end, int $stepMinutes, bool $showSeconds = false): array
    {
        $stepSeconds = $stepMinutes * 60;

        $timeValidator = app::getValidator('text_time');
        $timeValidator->reset();
        if (count($timeValidator->validate($start)) > 0) {
            return [];
        }
        $timeValidator->reset();
        if (count($timeValidator->validate($end)) > 0) {
            return [];
        }

        $rangeStart = explode(':', $start);
        $rangeEnd = explode(':', $end);
        $times = [];
        if (count($rangeStart) >= 2 && count($rangeStart) <= 3) {
            $rangeStartSeconds = self::getSecondsFromTimeArray($rangeStart);
            if (count($rangeEnd) >= 2 && count($rangeEnd) <= 3) {
                $rangeEndSeconds = self::getSecondsFromTimeArray($rangeEnd);
                $steps = floor(($rangeEndSeconds - $rangeStartSeconds) / $stepSeconds);
                for ($i = 0; $i <= $steps; $i++) {
                    $times[] = self::getTimeArrayFromSeconds($rangeStartSeconds + ($stepSeconds * $i));
                }
            }
        }

        $formattedTimes = [];
        foreach ($times as $t) {
            foreach ($t as &$c) {
                $c = str_pad($c, 2, '0', STR_PAD_LEFT);
            }
            $formattedTimes[] = implode(':', array_slice($t, 0, $showSeconds ? 3 : 2));
        }

        return $formattedTimes;
    }

    /**
     * returns time in seconds from a 2- or 3-element array
     */
    public static function getSecondsFromTimeArray(array $time): int
    {
        return self::getSecondsFromHours(intval($time[0])) + self::getSecondsFromMinutes($time[1]) + ((isset($time[2]) ? intval($time[2]) : 0));
    }

    /**
     * @param int $hours
     * @return int
     */
    public static function getSecondsFromHours(int $hours): int
    {
        return $hours * 60 * 60;
    }

    /**
     * @param int $minutes
     * @return int
     */
    public static function getSecondsFromMinutes(int $minutes): int
    {
        return $minutes * 60;
    }

    /**
     * @param int $seconds
     * @return array
     */
    public static function getTimeArrayFromSeconds(int $seconds): array
    {
        $hours = floor($seconds / (60 * 60));
        $minutes = floor(($seconds - self::getSecondsFromHours($hours)) / (60));
        $seconds = $seconds - (self::getSecondsFromHours($hours) + self::getSecondsFromMinutes($minutes));
        return [
          $hours,
          $minutes,
          $seconds,
        ];
    }
}
