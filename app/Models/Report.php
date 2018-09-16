<?php

namespace App\Models;

use App\Models\Report\Filters;
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
     * @param Filters $filters
     */
    public function apply(Filters $filters): void
    {
        $filters->all($this->logs);
    }

    /**
     * Report constructor.
     */
    public function __construct()
    {
        $teamId = Auth::user()->team_id;
        $this->logs = TimeLog::whereHas('user', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        });
    }

    /**
     * Generate report
     * @return array
     */
    abstract public function generate(): array;
}