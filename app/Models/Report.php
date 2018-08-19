<?php

namespace App\Models;

use App\Models\Report\Filters;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Class Report
 * @package App\Models
 */
class Report
{

    /**
     * @var Builder
     */
    private $logs;

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
     * @param Filters $filters
     */
    public function apply(Filters $filters): void
    {
        $filters->all($this->logs);
    }

    /**
     * @return array
     */
    public function generate(): array
    {
        $report = [];
        $items = [];
        $totalHours = 0;

        foreach($this->logs->get() as $key => $log) {
            $hours = round($log->duration / 60, 3);
            $totalHours += $hours;

            $billable = 'No';
            if (!$log->task || ($log->task && $log->task->billable)) {
                $billable = 'Yes';
            }

            $items[] = [
                'user_name' => $log->user->fullname,
                'client' => $log->project->client->company_name,
                'project' => $log->project->name,
                'task' => $log->task ? $log->task->name : 'none',
                'date' => $log->created_at->format('Y-m-d'),
                'hours' => $hours,
                'billable' => $billable,
                'in_progress' => (bool)$log->start,
            ];
        }

        $report['items'] = $items;
        $report['total_hours'] = round($totalHours, 3);

        return $report;
    }
}