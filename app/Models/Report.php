<?php

namespace App\Models;

use App\Models\Date\DateRange;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class Report
 * @package App\Models
 */
class Report
{

    /**
     * @var DateRange
     */
    private $range;

    /**
     * Report constructor.
     * @param DateRange $range
     */
    public function __construct(DateRange $range)
    {
        $this->range = $range;
    }

    /**
     * @return array
     */
    public function generate(): array
    {
        $report = [];
        $items = [];
        $totalHours = 0;

        foreach($this->getLogs() as $key => $log) {
            $hours = round($log->duration / 60, 2);
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
        $report['total_hours'] = round($totalHours, 2);

        return $report;
    }

    /**
     * @return Collection
     */
    private function getLogs(): Collection
    {
        $teamId = Auth::user()->team_id;
        /** @var Builder $logs */
        $logs = TimeLog::whereBetween('created_at', [$this->range->start(), $this->range->end()]);
        $logs->whereHas('user', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        });

        return $logs->get();
    }
}