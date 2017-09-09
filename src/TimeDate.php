<?php

use Carbon\Carbon;

/**
 * A class representing a timestamp
 */
class TimeDate extends Carbon
{
    const DATE_SHORT = "Y-m-d";
    const DATE_MEDIUM = "F d";
    const DATE_FULL = "F d, Y";
    const TIMESTAMP = "H:i";
    const TIMESTAMP_FULL = "H:i:s";
    const FULL = "Y-m-d H:i:s";
    const MYSQL = "Y-m-d H:i:s";

    /**
     * {@inheritdoc}
     */
    public function diffForHumans(Carbon $other = null, $absolute = false, $short = false)
    {
        if (self::diffInSeconds($other, true) < 4) {
            return "now";
        }
        return parent::diffForHumans($other, $absolute);
    }

    /**
     * Format the timestamp so that it can be used in mysql queries
     * @return string The formatted time
     */
    public function toMysql()
    {
        // We can't tell MySQL to store timezone data, so just convert the
        // timestamp to UTC
        $time = $this->copy()->setTimezone("UTC");

        return $time->format(self::MYSQL);
    }

    /**
     * Create a timestamp from a MySQL string
     * @param  string $time
     * @return TimeDate
     */
    public static function fromMysql($time)
    {
        return new self($time, "UTC");
    }

    /**
     * Create a timestamp from a string or another object
     * @param  string|DateTime $time
     * @return TimeDate
     */
    public static function from($time)
    {
        if ($time instanceof DateTime) {
            return self::instance($time);
        }

        return new self($time);
    }

    /**
     * Get a Carbon instance for the current date and time
     *
     * @param  DateTimeZone|string $timezone
     * @return TimeDate
     */
    public static function now($timezone = null)
    {
        return parent::now($timezone);
    }
}
