<?php


namespace App\Models;

/**
 * Class Report
 * @package App\Models
 */
class Report
{

    /**
     * @return array
     */
    public function generate(): array
    {
        $logs = TimeLog::all();
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
}