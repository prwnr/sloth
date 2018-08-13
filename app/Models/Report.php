<?php


namespace App\Models;

use App\Models\Date\DateRange;

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
        $logs = TimeLog::whereBetween('created_at', [$this->range->start(), $this->range->end()])->get();
        $report = [];
        $items = [];

        $totalHours = 0;
        foreach($logs as $key => $log) {
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
                'hours' => round($log->duration / 60, 2),
                'billable' => $billable,
                'in_progress' => (bool)$log->start,
            ];
        }

        $report['items'] = $items;
        $report['total_hours'] = $totalHours;

        return $report;
    }

    public function setDateRange(DateRange $range): void
    {

    }
}