<?php


namespace App\Models\Date;

use Carbon\Carbon;

/**
 * Class WeekRange
 * @package App\Models\Date
 */
class WeekRange extends DateRange
{

    /**
     * {@inheritdoc}
     */
    protected function initialize(): void
    {
        $this->start = Carbon::createFromFormat(self::FORMAT, $this->date)->startOfWeek();
        $this->end = Carbon::createFromFormat(self::FORMAT, $this->date)->endOfWeek();
    }
}