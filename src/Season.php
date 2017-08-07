<?php

use Carbon\Carbon;

class Season
{
    const WINTER = 'winter';
    const SPRING = 'spring';
    const SUMMER = 'summer';
    const FALL = 'fall';

    public static function getCurrentSeason()
    {
        $now = Carbon::now()->month;

        if (1 <= $now && $now <= 3) {
            return self::WINTER;
        } elseif (4 <= $now && $now <= 6) {
            return self::SPRING;
        } elseif (7 <= $now && $now <= 9) {
            return self::SUMMER;
        }

        return self::FALL;
    }

    public static function getCurrentSeasonRange()
    {
        $season = self::getCurrentSeason();

        switch ($season) {
            case self::WINTER:
                return self::getWinterSeason();

            case self::SPRING:
                return self::getSpringSeason();

            case self::SUMMER:
                return self::getSummerSeason();

            default:
                return self::getFallSeason();
        }
    }

    public static function getWinterSeason()
    {
        return (new MonthDateRange('January', 'March'));
    }

    public static function getSpringSeason()
    {
        return (new MonthDateRange('April', 'June'));
    }

    public static function getSummerSeason()
    {
        return (new MonthDateRange('July', 'September'));
    }

    public static function getFallSeason()
    {
        return (new MonthDateRange('October', 'December'));
    }
}
