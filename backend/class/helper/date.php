<?php
namespace codename\core\helper;

use \codename\core\app;

/**
 * helper class for date purposes.
 * @package codename\core
 * @since 2016-10-18
 */
class date extends \codename\core\helper {

  /**
   * formatting string
   * for displaying a datetime value as
   * YYYY-MM-DD HH:MM:SS
   * @var string
   */
	const DATETIME_WITHOUT_TIMEZONE = 'Y-m-d H:i:s';

    CONST INTERVAL_MONTH = 'month';
    CONST INTERVAL_YEAR = 'year';

    /**
     * Returns the timestamp of the current time
     * @return int
     */
    public static function getCurrentTimestamp() : int {
        return time();
    }

    /**
     * Returns the current date as a DB readible formatted date
     * @example 2013-10-01
     * @return string
     */
    public static function getCurrentDateAsDbdate() : string {
        return date('Y-m-d', self::getCurrentTimestamp());
    }

    /**
     * Returns the current date as a readible format.
     * <br />Uses the translation key DATETIME.FORMAT_DATE of your current localisation
     * @return string
     */
    public static function getCurrentDateAsReadible() : string {
        return date(app::translate('DATETIME.FORMAT_DATE'), self::getCurrentTimestamp());
    }

    /**
     * This method will return the given $timestamp's last day of month as a timestamp.
     * @param int $timestamp
     * @return string 
     */
    public static function getLastDayOfMonthByTimestampAsTimestamp(int $timestamp) : int {
        return strtotime(date('Y-m-t', $timestamp));
    }

    /**
     * This method will return the given $timestamp's month's last day as timestamp
     * @param int $year
     * @param int $month
     * @return string
     */
    public static function getLastDayOfMonthByYearAndMonthAsTimestamp(int $year, int $month) : int {
        return self::getLastDayOfMonthByTimestampAsTimestamp(strtotime("{$year}-{$month}-01"));
    }

    /**
     * This method will return the given $timestamp's
     * @param int $year
     * @param int $month
     * @return string
     */
    public static function getLastDayOfMonthByYearAndMonthAsDate(int $year, int $month) : string {
        return date('Y-m-d', self::getLastDayOfMonthByYearAndMonthAsTimestamp($year, $month));
    }

    /**
     * This method will return the given $timestamp's
     * @param int $year
     * @param int $month
     * @return string
     */
    public static function getLastDayOfMonthByYearAndMonthAsDay(int $year, int $month) : string {
        return date('d', self::getLastDayOfMonthByYearAndMonthAsTimestamp($year, $month));
    }

    /**
     * This method will return an array of timestamps that will differ from each other by the given $interval
     * @param int $start
     * @param int $end
     * @param string $interval
     * @return array
     */
    public static function getIntervalsFromStartUntilEnd(int $start, int $end, string $interval) : array {
        if($start > $end) {
          // do not allow a start date greater than end.
          return array();
        }
        $intervals = array($start);
        if(!in_array($interval, array(self::INTERVAL_MONTH, self::INTERVAL_YEAR))) {
            return $intervals;
        }
        while ($start < $end) {
            $start = strtotime(date('Y-m-d', $start) . ' +1 ' . $interval);
            if($start > $end) {
                break;
            }
            $intervals[] = $start;
        }
        return $intervals;
    }

    /**
     * This method will return an array of timestamps that will differ from each other by the given $interval
     * This function returns an array of unix timestamps
     * as an array( 'start' => ..., 'end' => ... )
     * @author Kevin Dargel
     * @param int $start
     * @param int $end
     * @param string $interval [e.g. self::INTERVAL_MONTH or self::INTERVAL_YEAR]
     * @return array
     */
    public static function getIntervalArrayFromStartUntilEnd(int $start, int $end, string $interval) : array {
        if($start > $end) {
          // do not allow a start date greater than end.
          return array();
        }
        $intervals = array();
        if(!in_array($interval, array(self::INTERVAL_MONTH, self::INTERVAL_YEAR))) {
            return $intervals;
        }
        $laststart = $start;
        while ($start < $end) {
            $start = strtotime(date('Y-m-d', $start) . ' +1 ' . $interval);
            if($start > $end) {
                break;
            }
            $intervals[] = array('start' => $laststart, 'end' => $start);
            $laststart = $start;
        }
        return $intervals;
    }

    /**
     * This function returns an array of unix timestamps when an article shall be invoiced & provisioned again
     * @param int $start
     * @param string $interval
     * @return array
     */
    public static function getIntervalsFromStartUntilNow(int $start, string $interval) : array {
        return self::getIntervalsFromStartUntilEnd($start, self::getCurrentTimestamp(), $interval);
    }

    /**
    * This function returns an array of unix timestamps
    * as an array( 'start' => ..., 'end' => ... )
    * @param int $start
    * @param string $interval
    * @return array
     */
    public static function getIntervalArrayFromStartUntilNow(int $start, string $interval) : array {
        return self::getIntervalArrayFromStartUntilEnd($start, self::getCurrentTimestamp(), $interval);
    }


    /**
     * Returns the current date as a DB-conform '2016-11-25 13:57:12' Format
     * @author Kevin Dargelk
     * @example 2016-11-25
     * @return string
     */
    public static function getCurrentDateTimeAsDbdate() : string {
        return date('Y-m-d H:i:s', self::getCurrentTimestamp());
    }

    /**
     * Returns the current date as a DB-conform '2016-11-25 13:57:12' Format
     * @author Kevin Dargel
     * @example 2016-12-09
     * @param int $timestamp [unix timestamp]
     * @return string
     */
    public static function getTimestampAsDbdate(int $timestamp) : string {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * returns a DateInterval object for two given points in time
     * @param string $start [date time as string]
     * @param string $end [date time as string]
     * @return \DateInterval
     */
    public static function getDateInterval(string $start, string $end) : \DateInterval {
      $start = new \DateTime($start);
      $end = new \DateTime($end);
      return $end->diff($start);
    }

    /**
     * returns an array of dates between start and end
     * @param string $start [date time as string]
     * @param string $end [date time as string]
     * @param string $useInterval [interval, PHP parseable]
     * @param string $format
     * @return string[] [array of string date(times)]
     */
    public static function getDateIntervalArray(string $start, string $end, string $useInterval = '+1 day', $format = 'Y-m-d') : array {
      // we may protect against backwards-jumping... or forward, if we're going backwards??
      $interval = \DateInterval::createFromDateString($useInterval);
      $startDate = new \DateTime($start);
      $endDate = new \DateTime($end);

      $result = array();
      $result[] = $startDate->format($format);

      $currentDate = $startDate->add($interval);
      while($currentDate <= $endDate) {
        $result[] = $currentDate->format($format);
        $currentDate = $currentDate->add($interval);
      }
      return $result;
    }
}
