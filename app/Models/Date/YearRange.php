<?php

namespace App\Models\Date;

use Carbon\Carbon;

/**
 * Class YearRange
 * @package App\Models\Date
 */
class YearRange extends DateRange
{

    /**
     * {@inheritdoc}
     */
    protected function initialize(): void
    {
        $this->start = Carbon::createFromFormat(self::FORMAT, $this->date)->startOfYear();
        $this->end = Carbon::createFromFormat(self::FORMAT, $this->date)->endOfYear();
    }
}