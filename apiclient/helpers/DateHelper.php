<?php

namespace app\helpers;

use App;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * Class DateHelper
 * @package helpers
 */
class DateHelper
{
   // const SYSTEM_DATE_FORMAT = 'Y m d';
    const SYSTEM_DATE_FORMAT = 'm d Y';
    const SYSTEM_DATE_FORMAT_JS = 'DD.MM.YYYY';
    const SYSTEM_TIME_FORMAT = 'H:i:s';
    const SYSTEM_DATETIME_FORMAT = 'Y-m-d H:i:s';
    const SYSTEM_DATETIME_FORMAT_JS = 'DD.MM.YYYY HH:mm';

    const DATE_NULL = '0000-00-00';
    const DATETIME_NULL = '0000-00-00 00:00:00';

    /**
     * Countries to date formats
     * @var array
     */
    public static $countriesToDateFormats = [
        'Albania' => 'Y-m-d',
        'United Arab Emirates' => 'd/m/Y',
        'Argentina' => 'd/m/Y',
        'Australia' => 'd/m/Y',
        'Austria' => 'd.m.Y',
        'Belgium' => 'd/m/Y',
        'Bulgaria' => 'Y-m-d',
        'Bahrain' => 'd/m/Y',
        'Bosnia and Herzegovina' => 'Y-m-d',
        'Belarus' => 'd.m.Y',
        'Bolivia' => 'd-m-Y',
        'Brazil' => 'd/m/Y',
        'Canada' => 'Y-m-d',
        'Switzerland' => 'd.m.Y',
        'Chile' => 'd-m-Y',
        'China' => 'Y-m-d',
        'Colombia' => 'd/m/Y',
        'Costa Rica' => 'd/m/Y',
        'Cyprus' => 'd/m/Y',
        'Czech Republic' => 'd.m.Y',
        'Germany' => 'd.m.Y',
        'Denmark' => 'd-m-Y',
        'Dominican Republic' => 'm/d/Y',
        'Algeria' => 'd/m/Y',
        'Ecuador' => 'd/m/Y',
        'Egypt' => 'd/m/Y',
        'Spain' => 'd/m/Y',
        'Estonia' => 'd.m.Y',
        'Finland' => 'd.m.Y',
        'France' => 'd/m/Y',
        'United Kingdom' => 'd/m/Y',
        'Greece' => 'd/m/Y',
        'Guatemala' => 'd/m/Y',
        'Hong Kong' => 'Y/m/d',
        'Honduras' => 'm-d-Y',
        'Croatia' => 'd.m.Y',
        'Hungary' => 'Y.m.d',
        'Indonesia' => 'd/m/Y',
        'India' => 'd/m/Y',
        'Ireland' => 'd/m/Y',
        'Iraq' => 'd/m/Y',
        'Iceland' => 'd.m.Y',
        'Israel' => 'd/m/Y',
        'Italy' => 'd/m/Y',
        'Jordan' => 'd/m/Y',
        'Japan' => 'Y/m/d',
        'South Korea' => 'Y.m.d',
        'Kuwait' => 'd/m/Y',
        'Lebanon' => 'd/m/Y',
        'Libya' => 'd/m/Y',
        'Lithuania' => 'Y.m.d',
        'Luxembourg' => 'd/m/Y',
        'Latvia' => 'Y.d.m',
        'Morocco' => 'd/m/Y',
        'Mexico' => 'd/m/Y',
        'Macedonia' => 'd.m.Y',
        'Malta' => 'd/m/Y',
        'Montenegro' => 'd.m.Y',
        'Malaysia' => 'd/m/Y',
        'Nicaragua' => 'm-d-Y',
        'Netherlands' => 'd-m-Y',
        'Norway' => 'd.m.Y',
        'New Zealand' => 'd/m/Y',
        'Oman' => 'd/m/Y',
        'Panama' => 'm/d/Y',
        'Peru' => 'd/m/Y',
        'Philippines' => 'm/d/Y',
        'Poland' => 'd.m.Y',
        'Puerto Rico' => 'm-d-Y',
        'Portugal' => 'd-m-Y',
        'Paraguay' => 'd/m/Y',
        'Qatar' => 'd/m/Y',
        'Romania' => 'd.m.Y',
        'Russia' => 'd.m.Y',
        'Saudi Arabia' => 'd/m/Y',
        'Serbia and Montenegro' => 'd.m.Y',
        'Sudan' => 'd/m/Y',
        'Singapore' => 'd/m/Y',
        'El Salvador' => 'm-d-Y',
        'Serbia' => 'd.m.Y',
        'Slovakia' => 'd.m.Y',
        'Slovenia' => 'd.m.Y',
        'Sweden' => 'Y-m-d',
        'Syria' => 'd/m/Y',
        'Thailand' => 'd/m/Y',
        'Tunisia' => 'd/m/Y',
        'Turkey' => 'd.m.Y',
        'Taiwan' => 'Y/m/d',
        'Ukraine' => 'd.m.Y',
        'Uruguay' => 'd/m/Y',
        'United States' => 'm/d/Y',
        'Venezuela' => 'd/m/Y',
        'Vietnam' => 'd/m/Y',
        'Yemen' => 'd/m/Y',
        'South Africa' => 'Y/m/d',
    ];

    public static function getFormattedDateFromString($format, $string)
    {
        return self::formatDate(date($format, strtotime($string)));
    }


    public static function isNull($date)
    {
        if ($date == '0000-00-00' or $date == '0000-00-00 00:00:00') {
            return true;
        } else {
            return false;
        }
    }

    public static function validate($date, $format = 'Y-m-d')
    {
        // Check empty date
        if (static::isNull($date)) {
            return true;
        }
        $d = \DateTime::createFromFormat($format, $date);
        $result = $d && $d->format($format) == $date;
        return $result;
    }

    public static function validateDate($date, $format = 'Y-m-d')
    {
        return self::validate($date, $format);
    }

    public static function validateDatetime($datetime, $format = 'Y-m-d H:i:s')
    {
        return self::validate($datetime, $format);
    }

    public static function validateTime($time, $format = 'H:i:s')
    {
        return self::validate($time, $format);
    }

    /**
     * Check if date in past.
     * @param string $date
     * @param null|string $currentDate Custom current date
     * @return bool
     */
    public static function checkIfDateInPast($date, $currentDate = null)
    {
        if (empty($currentDate)) {
            $currentDate = date('Y-m-d');
        }

        $datetime1 = new DateTime($date);
        $datetime2 = new DateTime($currentDate);

        if ($datetime1 < $datetime2) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Check if datetime in past.
     * @param string $datetime
     * @param null|string $currentDatetime Custom current datetime
     * @return bool
     */
    public static function checkIfDatetimeInPast($datetime, $currentDatetime = null)
    {
        if (empty($currentDatetime)) {
            $currentDatetime = date('Y-m-d H:i:s');
        }

        $datetime1 = new DateTime($datetime);
        $datetime2 = new DateTime($currentDatetime);

        return $datetime1 < $datetime2;
    }

    public static function checkIfDateAreEqual($date1, $date2)
    {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);

        if ($datetime1 == $datetime2) {
            return true;
        }

        return false;
    }

    public static function validatePeriod($date1, $date2, $allowSameDay = false)
    {
        if (static::validate($date1) == false or static::validate($date2) == false) {
            return false;
        }

        // Check empty date
        if (static::isNull($date2)) {
            return true;
        }

        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);

        if ($allowSameDay == true) {
            if ($datetime1 <= $datetime2) {
                return true;
            }
        } else {
            if ($datetime1 < $datetime2) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $date
     * @param string $format
     * @return bool|string
     */
    public static function formatDate($date, $format = 'Y-m-d')
    {
        $time = strtotime($date);

        if ($time == false) {
            return false;
        }

        $date = date($format, $time);

        if (self::validate($date)) {
            return $date;
        } else {
            return false;
        }
    }

    public static function timeDuration($seconds, $use = null, $zeros = false)
    {
        $periods = [
            'h' => 3600,
            'm' => 60,
            's' => 1
        ];
        $array = [];

        // Break into periods
        $seconds = (float)$seconds;
        foreach ($periods as $period => $value) {
            if ($use && strpos($use, $period[0]) === false) {
                continue;
            }
            $count = floor($seconds / $value);
            if ($count == 0 && !$zeros) {
                continue;
            }
            $segments[strtolower($period)] = $count;
            $seconds = $seconds % $value;
        }

        if (isset($segments) && is_array($segments)) {
            foreach ($segments as $key => $value) {
                if ($value < 10) {
                    $value = "0" . $value;
                }
                $array[$key] = $value;
            }
        }

        foreach ($periods as $k => $v) {
            if (!isset($array[$k])) {
                $array[$k] = "00";
            }
        }

        $str = $array['h'] . ":" . $array['m'] . ":" . $array['s'];
        return $str;
    }

    /**
     * Get yesterday date.
     * @param null|string $currentDate Get yesterday for this date.
     * @return false|string
     */
    public static function getYesterday($currentDate = null)
    {
        if (empty($currentDate)) {
            $currentDate = date('Y-m-d');
        }
        return date('Y-m-d', strtotime('-1 day', strtotime($currentDate)));
    }

    /**
     * Eng: returns +1 day from today
     * @param null|string $currentDate Get tomorrow for this date.
     * @return string
     */
    public static function getTomorrow($currentDate = null)
    {
        if (empty($currentDate)) {
            $currentDate = date('Y-m-d');
        }
        return date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
    }

    public static function sec2hms($sec, $padHours = true)
    {
        $hms = "";
        $hours = intval(intval($sec) / 3600);
        $hms .= ($padHours)
            ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":"
            : $hours . ":";
        $minutes = intval(($sec / 60) % 60);
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";
        $seconds = intval($sec % 60);
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
        return $hms;
    }

    /**
     * Convert second to day:hour:minutes:second
     * @param $sec
     * @param bool $padHours
     * @return string
     */
    public static function sec2dhms($sec, $padHours = true)
    {
        $days = intval($sec / 86400);
        $sec = $sec - ($days * 86400);
        return $days . ' ' . static::sec2hms($sec, $padHours);
    }

    public static function hms2sec($hms)
    {
        return strtotime($hms) - strtotime('TODAY');
    }

    public static function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return (float)$usec + (float)$sec;
    }

    public static function processingTime($t)
    {
        $sh = str_replace(["h", "m", "s"], ["hour", "minute", "second"], $t);
        $t = time();
        $tt = strtotime("+$sh") - $t;
        return self::sec2hms($tt);
    }

    public static function curdate($date = null)
    {
        return static::getDate($date);
    }

    public static function now($datetime = null)
    {
        return static::getDatetime($datetime);
    }

    /**
     * @param string $format Date format
     * @return string
     * @throws Exception
     */
    public static function getDateWithMilliseconds($format = 'Y-m-d H:i:s.u')
    {
        $d = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        try {
            $timezone = new DateTimeZone(\Yii::$app->getTimeZone());
            $d->setTimezone($timezone);
        } catch (Exception $exception) {
            unset($exception);
        }

        return $d->format($format);
    }

    public static function getDatetime($datetime = null)
    {
        return date('Y-m-d H:i:s', $datetime === null ? time() : strtotime($datetime));
    }

    public static function getDate($date = null)
    {
        return date('Y-m-d', $date === null ? time() : strtotime($date));
    }

    public static function getTime($time = null)
    {
        return date('H:i:s', $time === null ? time() : strtotime($time));
    }

    public static function addSecondsToDatetime($datetime, $seconds)
    {
        $time = strtotime($datetime);
        $newTime = $time + $seconds;
        return date('Y-m-d H:i:s', $newTime);
    }

    public static function inInterval($from, $to, $t)
    {
        $dt1 = DateTime::createFromFormat('G:i', $from);
        $dt2 = DateTime::createFromFormat('G:i', $to);
        $dt = DateTime::createFromFormat('G:i', $t);

        if ($dt1 == null || $dt2 == null || $dt == null) {
            return null;
        }

        if ($dt2 < $dt1) {
            return !($dt2 <= $dt && $dt <= $dt1);
        } else {
            return $dt1 <= $dt && $dt <= $dt2;
        }
    }

    public static function convertDateFormat($date, $formatTo, $formatFrom = null)
    {
        if (!is_string($date) or self::isNull($date)) {
            return null;
        }

        try {
            if ($formatFrom === null) {
                $object = new DateTime($date);
            } else {
                $object = DateTime::createFromFormat($formatFrom, $date);
                if (!is_object($object)) {
                    return null;
                }
            }
            return $object->format($formatTo);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function convertTimeFormat($time, $format)
    {
        return date($format, strtotime($time));
    }

    public static function convertDateTimeFormat($datetime, $format)
    {
        //empty value will convert to 1970-01-01 https://jira.splynx.com/browse/SPL-1654
        if ($datetime == '0000-00-00 00:00:00' || empty($datetime)) {
            return null;
        }
        return self::convertTimeFormat($datetime, $format);
    }

    /**
     * Add days to date
     * @param $date
     * @param $days
     * @param string $format
     * @return bool|string
     */
    public static function addDaysToDate($date, $days, $format = self::SYSTEM_DATE_FORMAT)
    {
        if (is_numeric($days)) {
            $date = new DateTime($date);
            $date->modify('+' . intval($days) . ' day');
            return $date->format($format);
        }
        return false;
    }

    /**
     * Calculate date between two dates
     * @param string $dateTo Date to
     * @param string|null $dateFrom Date from (if null - today will be used)
     * @return string
     */
    public static function calculateDaysBetweenDates($dateTo, $dateFrom = null)
    {
        if ($dateFrom === null) {
            $dateFrom = static::getDate();
        }

        $dateFromModel = new DateTime($dateFrom);
        $dateToModel = new DateTime($dateTo);

        return $dateToModel->diff($dateFromModel)->format("%a");
    }

    /**
     * Check if date between date range
     * @param DateTime|DateTimeImmutable $date Needle date
     * @param DateTime|DateTimeImmutable $startDate Range start date
     * @param DateTime|DateTimeImmutable $endDate Range end date
     * @return bool True if date in range
     */
    public static function isDateInRange(DateTimeInterface $date, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        return $date > $startDate && $date < $endDate;
    }

    /**
     * Check if date between date range (include given date)
     * @param DateTime $date Needle date
     * @param DateTime $startDate Range start date
     * @param DateTime $endDate Range end date
     * @return bool True if date in range
     */
    public static function isDateInRangeIncluded(DateTime $date, DateTime $startDate, DateTime $endDate)
    {
        return $date >= $startDate && $date <= $endDate;
    }

    /**
     * Add months to date.
     * @param int $months How much of month need add to date
     * @param DateTime $date
     * @return mixed
     * @throws \Exception
     */
    public static function addMonth($months, $date)
    {
        $result = new DateTime($date->format('Y-m-d'));
        $result->modify('last day of +' . $months . ' month');

        if ($date->format('d') > $result->format('d')) {
            $interval = $date->diff($result);
            return $date->add($interval);
        } else {
            $interval = new DateInterval('P' . $months . 'M');
            return $date->add($interval);
        }
    }

    /**
     * Check if now is weekend
     * @param string|null $date If set - check if date is weekend
     * @return bool
     */
    public static function isWeekend($date = null)
    {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }

        return (date('N', strtotime($date)) >= 6);
    }

    /**
     * @param $seconds
     * @param array $arguments accepted value ['d', 'h', 'm', 's']
     * @return bool|string
     */
    public static function convertSecondsToWDMHS($seconds, $arguments = [])
    {
        if ($seconds == 0) {
            return false;
        }

        $secondsAssociation = [
            //'w' => (3600 * 24 * 7),
            'd' => (3600 * 24),
            'h' => 3600,
            'm' => 60,
        ];

        if (!empty($arguments)) {
            foreach ($secondsAssociation as $key => $item) {
                if (!in_array($key, $arguments)) {
                    unset($secondsAssociation[$key]);
                }
            }
        }

        $timeOutput[] = '';
        foreach ($secondsAssociation as $key => $value) {
            if (($amountCount = intval(intval($seconds) / $value)) !== 0) {
                $seconds -= $amountCount * $value;
                $timeOutput[] = $amountCount . $key;
            }
        }

        if ($seconds != 0 and in_array('s', $arguments)) {
            $timeOutput[] = $seconds . 's ';
        }

        return trim(implode(' ', $timeOutput));
    }

    /**
     * @return bool
     * @throws \exceptions\InvalidConfig
     * @throws \exceptions\InvalidSQL
     */
    public static function getIsSynchronizedDateTime()
    {
        $systemTimeArray = self::getSystemDateTimeArray();
        $mysqlTimestamp = strtotime($systemTimeArray['mysql']);
        $systemTimestamp = strtotime($systemTimeArray['system']);
        $phpTimestamp = strtotime($systemTimeArray['php']);
        return (abs($systemTimestamp - $phpTimestamp) < 120 and abs($phpTimestamp - $mysqlTimestamp) < 120);
    }

    private static $_systemDateTimeArray;

    /**
     * @return array
     * @throws \exceptions\InvalidConfig
     * @throws \exceptions\InvalidSQL
     */
    public static function getSystemDateTimeArray()
    {
        if (self::$_systemDateTimeArray === null) {
            $db = App::$app->getDb();
            $mysql = $db->rawQuery("SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i') as n;");
            exec("date +'%Y-%m-%d %H:%M'", $system);
            self::$_systemDateTimeArray = [
                'system' => trim($system[0]),
                'php' => trim(date('Y-m-d H:i')),
                'mysql' => trim($mysql[0]['n']),
            ];
        }
        return self::$_systemDateTimeArray;
    }

    /**
     * @param int $seconds
     * @return string
     */
    public static function seconds2His($seconds)
    {
        $hours = 0;

        if ($seconds > 3600) {
            $hours = floor($seconds / 3600);
        }
        $seconds %= 3600;

        return str_pad($hours, 2, '0', STR_PAD_LEFT)
            . gmdate(':i:s', $seconds);
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public static function isValidTimeStamp($timestamp)
    {
        return ((string)(int)$timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
