<?php

use Carbon\Carbon;

class MonthDateRange
{
    private $start;
    private $end;

    public function __construct($start, $end)
    {
        $this->start = new Carbon('first of ' . $start);
        $this->end = new Carbon('last of '. $end);
    }

    /**
     * @param int|null $year
     *
     * @return Carbon
     */
    public function getStartOfRange($year = null)
    {
        $year = ($year === null) ? Carbon::now()->year : $year;

        return $this->start->copy()->year($year);
    }

    /**
     * @param int|null $year
     *
     * @return Carbon
     */
    public function getEndOfRange($year = null)
    {
        $year = ($year === null) ? Carbon::now()->year : $year;

        return $this->end->copy()->year($year);
    }
}
