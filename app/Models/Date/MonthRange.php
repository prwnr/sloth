<?php


namespace App\Models\Date;

use Carbon\Carbon;

/**
 * Class MonthRange
 * @package App\Models\Date
 */
class MonthRange extends DateRange
{

    /**
     * {@inheritdoc}
     */
    protected function initialize(): void
    {
        $this->start = Carbon::createFromFormat(self::FORMAT, $this->date)->startOfMonth();
        $this->end = Carbon::createFromFormat(self::FORMAT, $this->date)->endOfMonth();
    }
}