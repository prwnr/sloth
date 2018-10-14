<?php

namespace App\Models\Report;

use App\Models\Date\DateRange;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PeriodicReport
 * @package App\Models\Report
 */
abstract class PeriodicReport extends Report
{

    /**
     * @var string
     */
    protected $period;

    /**
     * @var Collection
     */
    protected $items;

    /**
     * PeriodicReport constructor.
     * @param array $filterOptions
     * @param string $period
     */
    public function __construct(array $filterOptions, ?string $period = null)
    {
        $this->period = $period;
        $filterOptions['range'] = $this->period ?: DateRange::YEAR;
        parent::__construct($filterOptions);
    }

    /**
     * @return array
     */
    protected function preparePeriodsArray(): array
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
    protected function makeLabels(): array
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

    /**
     * @return Collection
     */
    protected function groupItems(): Collection
    {
        $groupFormat = $this->period === DateRange::YEAR ? 'm' : 'd';
        return $this->logs->get()->groupBy(function ($query) use ($groupFormat) {
            return Carbon::parse($query->created_at)->format($groupFormat);
        });
    }
}