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

    /**
     * @return array
     */
    private function preparePeriodsArray(): array
    {
        if ($this->period === DateRange::MONTH) {
            return array_fill(1, Carbon::now()->daysInMonth, 0);
        }

        if ($this->period === DateRange::YEAR) {
            return array_fill(1, 12, 0);
        }

        return array_fill(0, 7, 0);
    }

    /**
     * @return array
     */
    private function makeLabels(): array
    {
        if ($this->period === DateRange::MONTH) {
            return array_keys(array_fill(1, Carbon::now()->daysInMonth, 0));
        }

        if ($this->period === DateRange::YEAR) {
            $months = [];
            for ($i = 1; $i <= 12; $i++) {
                $months[] = Carbon::createFromFormat('m', $i)->format('F');
            }
            return $months;
        }

        return first_to_last(Carbon::getDays());
    }
}