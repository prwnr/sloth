<?php

namespace App\Models\Report;

use App\Models\Date\DateRange;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class HoursReport
 * @package App\Models\Report
 */
class HoursReport extends Report
{

    /**
     * @var string
     */
    private $period;

    /**
     * @param array $options
     */
    public function addFilters(array $options): void
    {
        $options['range'] = $this->period ?: DateRange::YEAR;
        parent::addFilters($options);
    }

    /**
     * @param string $period
     */
    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $groupFormat = $this->period === DateRange::YEAR ? 'm' : 'd';
        /** @var Collection $items */
        $items = $this->logs->get()->groupBy(function ($query) use ($groupFormat) {
            return Carbon::parse($query->created_at)->format($groupFormat);
        });

        $totalHours = $this->getPeriodsArray();
        foreach ($items as $period => $item) {
            $sum = $item->sum('duration');
            $number = ltrim($period, '0');
            if ($this->period === DateRange::WEEK) {
                $number = Carbon::createFromFormat('d', $number)->dayOfWeek;
            }
            $totalHours[$number] = round($sum / 60, 3);
        }

        ksort($totalHours);
        return [
            'hours' => array_values($totalHours),
            'labels' => $this->getPeriodLabels()
        ];
    }

    /**
     * @return array
     */
    private function getPeriodsArray(): array
    {
        if ($this->period === DateRange::MONTH) {
            return array_fill(1, Carbon::now()->daysInMonth, 0);
        }

        if ($this->period === DateRange::YEAR) {
            return array_fill(1, 12, 0);
        }

        return array_fill(1, 7, 0);
    }

    /**
     * @return array
     */
    private function getPeriodLabels(): array
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

        return Carbon::getDays();
    }
}