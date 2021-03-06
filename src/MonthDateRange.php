<?php

use Carbon\Carbon;

class MonthDateRange
{
    private $start;
    private $end;

    public function __construct($start, $end)
    {
        $this->start = new Carbon('first day of ' . $start);
        $this->end = new Carbon('last day of '. $end);
    }

    /**
     * @param int|null $year
     *
     * @return Carbon
     */
    public function getStartOfRange($year = null)
    {
        $year = ($year === null) ? Carbon::now()->year : $year;

        return $this->start->year($year)->copy();
    }

    /**
     * @param int|null $year
     *
     * @return Carbon
     */
    public function getEndOfRange($year = null)
    {
        $year = ($year === null) ? Carbon::now()->year : $year;

        return $this->end->year($year)->copy();
    }
}
