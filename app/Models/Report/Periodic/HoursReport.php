<?php

namespace App\Models\Report\Periodic;

use App\Models\Date\DateRange;
use App\Models\Report\PeriodicReport;
use Carbon\Carbon;

/**
 * Class HoursReport
 * @package App\Models\Report
 */
class HoursReport extends PeriodicReport
{

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $items = $this->groupItems();
        $totalHours = $this->preparePeriodsArray();
        foreach ($items as $period => $item) {
            $sum = $item->sum('duration');
            $number = ltrim($period, '0');
            if ($this->period === DateRange::WEEK) {
                $number = Carbon::createFromFormat('d', $number)->dayOfWeek;
            }
            $totalHours[$number] = round($sum / 60, 3);
        }


        if ($this->period === DateRange::WEEK) {
            $totalHours = first_to_last($totalHours);
        }

        return [
            'hours' => array_values($totalHours),
            'labels' => $this->makeLabels()
        ];
    }
}