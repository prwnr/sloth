<?php

namespace App\Models;

use App\Models\Report\LogsFilter;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Class Report
 * @package App\Models
 */
abstract class Report
{

    /**
     * @var Builder
     */
    protected $logs;

    /**
     * @var LogsFilter
     */
    protected $filters;

    /**
     * Report constructor.
     * @param array $filterOptions
     */
    public function __construct(array $filterOptions)
    {
        $teamId = Auth::user()->team_id;
        $this->logs = TimeLog::whereHas('member', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        });

        $this->filters = new LogsFilter($this->logs);
        $this->filters->setOptions($filterOptions);
        $this->filters->applyOptions();
    }

    /**
     * Generate report
     * @return array
     */
    abstract public function generate(): array;
}