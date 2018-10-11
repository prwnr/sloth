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