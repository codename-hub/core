<?php

namespace codename\core\helper;

use codename\core\app;
use codename\core\exception;
use codename\core\helper;
use DateInterval;
use DateTime;
use ReflectionException;

/**
 * helper class for date purposes.
 * @package codename\core
 * @since 2016-10-18
 */
class date extends helper
{
    /**
     * formatting string
     * for displaying a datetime value as
     * YYYY-MM-DD HH:MM:SS
     * @var string
     */
    public const DATETIME_WITHOUT_TIMEZONE = 'Y-m-d H:i:s';

    public const INTERVAL_MONTH = 'month';
    public const INTERVAL_YEAR = 'year';

    /**
     * Returns the current date as a DB readable formatted date
     * @return string
     * @example 2013-10-01
     */
    public static function getCurrentDateAsDbdate(): string
    {
        return date('Y-m-d', self::getCurrentTimestamp());
    }

    /**
     * Returns the timestamp of the current time
     * @return int
     */
    public static function getCurrentTimestamp(): int
    {
        return time();
    }

    /**
     * Returns the current date as a readable format.
     * Uses the translation key DATETIME.FORMAT_DATE of your current localisation
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    public static function getCurrentDateAsReadible(): string
    {
        return date(app::translate('DATETIME.FORMAT_DATE'), self::getCurrentTimestamp());
    }

    /**
     * This method will return the given $timestamp's
     * @param int $year
     * @param int $month
     * @return string
     */
    public static function getLastDayOfMonthByYearAndMonthAsDate(int $year, int $month): string
    {
        return date('Y-m-d', self::getLastDayOfMonthByYearAndMonthAsTimestamp($year, $month));
    }

    /**
     * This method will return the given $timestamp's month's last day as timestamp
     * @param int $year
     * @param int $month
     * @return int
     */
    public static function getLastDayOfMonthByYearAndMonthAsTimestamp(int $year, int $month): int
    {
        return self::getLastDayOfMonthByTimestampAsTimestamp(strtotime("$year-$month-01"));
    }

    /**
     * This method will return the given $timestamp's last day of month as a timestamp.
     * @param int $timestamp
     * @return int
     */
    public static function getLastDayOfMonthByTimestampAsTimestamp(int $timestamp): int
    {
        return strtotime(date('Y-m-t', $timestamp));
    }

    /**
     * This method will return the given $timestamp's
     * @param int $year
     * @param int $month
     * @return string
     */
    public static function getLastDayOfMonthByYearAndMonthAsDay(int $year, int $month): string
    {
        return date('d', self::getLastDayOfMonthByYearAndMonthAsTimestamp($year, $month));
    }

    /**
     * This function returns an array of unix timestamps when an article shall be invoiced & provisioned again
     * @param int $start
     * @param string $interval
     * @return array
     */
    public static function getIntervalsFromStartUntilNow(int $start, string $interval): array
    {
        return self::getIntervalsFromStartUntilEnd($start, self::getCurrentTimestamp(), $interval);
    }

    /**
     * This method will return an array of timestamps that will differ from each other by the given $interval
     * @param int $start
     * @param int $end
     * @param string $interval
     * @return array
     */
    public static function getIntervalsFromStartUntilEnd(int $start, int $end, string $interval): array
    {
        if ($start > $end) {
            // do not allow a start date greater than end.
            return [];
        }
        $intervals = [$start];
        if (!in_array($interval, [self::INTERVAL_MONTH, self::INTERVAL_YEAR])) {
            return $intervals;
        }
        while ($start < $end) {
            $start = strtotime(date('Y-m-d', $start) . ' +1 ' . $interval);
            if ($start > $end) {
                break;
            }
            $intervals[] = $start;
        }
        return $intervals;
    }

    /**
     * This function returns an array of unix timestamps
     * as an array( 'start' => ..., 'end' => ... )
     * @param int $start
     * @param string $interval
     * @return array
     */
    public static function getIntervalArrayFromStartUntilNow(int $start, string $interval): array
    {
        return self::getIntervalArrayFromStartUntilEnd($start, self::getCurrentTimestamp(), $interval);
    }

    /**
     * This method will return an array of timestamps that will differ from each other by the given $interval
     * This function returns an array of unix timestamps
     * as an array( 'start' => ..., 'end' => ... )
     * @param int $start
     * @param int $end
     * @param string $interval [e.g. self::INTERVAL_MONTH or self::INTERVAL_YEAR]
     * @return array
     */
    public static function getIntervalArrayFromStartUntilEnd(int $start, int $end, string $interval): array
    {
        if ($start > $end) {
            // do not allow a start date greater than end.
            return [];
        }
        $intervals = [];
        if (!in_array($interval, [self::INTERVAL_MONTH, self::INTERVAL_YEAR])) {
            return $intervals;
        }
        $laststart = $start;
        while ($start < $end) {
            $start = strtotime(date('Y-m-d', $start) . ' +1 ' . $interval);
            if ($start > $end) {
                break;
            }
            $intervals[] = ['start' => $laststart, 'end' => $start];
            $laststart = $start;
        }
        return $intervals;
    }

    /**
     * Returns the current date as a DB-conform '2016-11-25 13:57:12' Format
     * @return string
     * @example 2016-11-25k
     */
    public static function getCurrentDateTimeAsDbDate(): string
    {
        return date('Y-m-d H:i:s', self::getCurrentTimestamp());
    }

    /**
     * Returns the current date as a DB-conform '2016-11-25 13:57:12' Format
     * @param int $timestamp [unix timestamp]
     * @return string
     * @example 2016-12-09
     */
    public static function getTimestampAsDbdate(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * returns a DateInterval object for two given points in time
     * @param string $start [date time as string]
     * @param string $end [date time as string]
     * @return DateInterval
     * @throws \Exception
     */
    public static function getDateInterval(string $start, string $end): DateInterval
    {
        $start = new DateTime($start);
        $end = new DateTime($end);
        return $end->diff($start);
    }

    /**
     * returns an array of dates between start and end
     * @param string $start [date time as string]
     * @param string $end [date time as string]
     * @param string $useInterval [interval, PHP parseable]
     * @param string $format
     * @return string[] [array of string date(times)]
     * @throws \Exception
     */
    public static function getDateIntervalArray(string $start, string $end, string $useInterval = '+1 day', string $format = 'Y-m-d'): array
    {
        // we may protect against backwards-jumping... or forward, if we're going backwards??
        $interval = DateInterval::createFromDateString($useInterval);
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);

        $result = [];
        $result[] = $startDate->format($format);

        $currentDate = $startDate->add($interval);
        while ($currentDate <= $endDate) {
            $result[] = $currentDate->format($format);
            $currentDate = $currentDate->add($interval);
        }
        return $result;
    }

    /**
     * [getISO8601FromRelativeDatetimeString description]
     * @param string $relativeDatetime [description]
     * @return string                   [description]
     */
    public static function getISO8601FromRelativeDatetimeString(string $relativeDatetime): string
    {
        return self::getISO8601FromDateInterval(DateInterval::createFromDateString($relativeDatetime));
    }

    /**
     * [getISO8601FromDateInterval description]
     * @param DateInterval $dateInterval [description]
     * @return string                     [description]
     */
    public static function getISO8601FromDateInterval(DateInterval $dateInterval): string
    {
        //
        // @see https://stackoverflow.com/questions/33787039/format-dateinterval-as-iso8601
        //

        [$date, $time] = explode("T", $dateInterval->format("P%yY%mM%dDT%hH%iM%sS"));
        // now, we need to remove anything that is a zero, but make sure to not remove
        // something like 10D or 20D
        $res =
          str_replace(['M0D', 'Y0M', 'P0Y'], ['M', 'Y', 'P'], $date) .
          rtrim(str_replace(['M0S', 'H0M', 'T0H'], ['M', 'H', 'T'], "T$time"), "T");
        if ($res == 'P') { // edge case - if we remove everything, DateInterval will hate us later
            return 'PT0S';
        }
        return $res;
    }
}
