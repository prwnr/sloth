<?php


namespace App\Models\Date;

use Carbon\Carbon;

/**
 * Class DateRangeFactory
 * @package App\Models\Date
 */
class DateRangeFactory
{

    /**
     * @var array
     */
    private $ranges = [
        DateRange::WEEK => WeekRange::class,
        DateRange::MONTH => MonthRange::class,
        DateRange::YEAR => YearRange::class
    ];

    /**
     * @param string $name
     * @return DateRange
     */
    public function make(string $name): DateRange
    {
        $carbon = Carbon::now()->toTimeString();
        if (isset($this->ranges[$name])) {
            return new $this->ranges[$name]($carbon);
        }

        return new $this->ranges[DateRange::WEEK]($carbon);
    }
}