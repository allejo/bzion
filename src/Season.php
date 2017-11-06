<?php

use Carbon\Carbon;

abstract class Season
{
    const WINTER = 'winter';
    const SPRING = 'spring';
    const SUMMER = 'summer';
    const FALL = 'fall';

    public static function toInt($season)
    {
        switch ($season) {
            case self::WINTER:
                return 1;

            case self::SPRING:
                return 2;

            case self::SUMMER:
                return 3;

            case self::FALL:
                return 4;

            default:
                return -1;
        }
    }

    public static function getSeason(DateTime $dateTime)
    {
        return [
            'season' => self::getCurrentSeason((int)$dateTime->format('n')),
            'year'   => (int)$dateTime->format('Y'),
        ];
    }

    public static function getCurrentSeason($month = null)
    {
        if ($month === null) {
            $month = Carbon::now()->month;
        }

        if (1 <= $month && $month <= 3) {
            return self::WINTER;
        } elseif (4 <= $month && $month <= 6) {
            return self::SPRING;
        } elseif (7 <= $month && $month <= 9) {
            return self::SUMMER;
        }

        return self::FALL;
    }

    public static function getCurrentSeasonRange($season = null)
    {
        if ($season === null) {
            $season = self::getCurrentSeason();
        }

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
