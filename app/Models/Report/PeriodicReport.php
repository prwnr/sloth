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
     * @return Collection
     */
    public function gatherItems(): Collection
    {
        $groupFormat = $this->period === DateRange::YEAR ? 'm' : 'd';
        return $this->logs->get()->groupBy(function ($query) use ($groupFormat) {
            return Carbon::parse($query->created_at)->format($groupFormat);
        });
    }
}