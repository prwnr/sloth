<?php

namespace App\Models;

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