<?php

/**
 * A class representing a timestamp
 */
class TimeDate extends Carbon\Carbon {

    /**
     * Get the time difference in a human readable format.
     * @param TimeDate $other
     * @return string
     */
    public function diffForHumans(Carbon\Carbon $other = null) {
        if (self::diffInSeconds($other, true) < 4)
                return "now";
        return parent::diffForHumans($other);
    }
}
