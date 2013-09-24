<?php

class TimeDate extends Carbon\Carbon {
    public function diffForHumans(Carbon\Carbon $other = null) {
        if (self::diffInSeconds($other, true) < 4)
                return "now";
        return parent::diffForHumans($other);
    }
}
