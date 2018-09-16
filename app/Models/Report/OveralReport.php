<?php

namespace App\Models\Report;

use App\Models\Report;
use App\Models\TimeLog;

/**
 * Class OveralReport
 * @package App\Models\Report
 */
class OveralReport extends Report
{

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $report = [];
        $items = [];
        $totalHours = 0;
        $totalBillableHours = 0;
        $salaryTotals = [];

        /**
         * @var int $key
         * @var TimeLog $log
         */
        foreach($this->logs->get() as $key => $log) {
            $rowHours = $log->duration();
            $totalHours += $rowHours;
            $totalBillableHours += $log->isBillabe() ? $log->duration() : 0.0;
            $this->sumSalary($log, $salaryTotals);

            $items[] = [
                'user_name' => $log->user->fullname,
                'client' => $log->project->client->company_name,
                'project' => $log->project->name,
                'task' => $log->task ? $log->task->name : 'none',
                'date' => $log->created_at->format('Y-m-d'),
                'duration' => $rowHours,
                'billable' => $log->isBillabe() ? 'Yes' : 'No',
                'in_progress' => (bool)$log->start,
            ];
        }

        foreach($salaryTotals as $currency => $salary) {
            $salaryTotals[$currency] = round($salary, 2);
        }

        $report['items'] = $items;
        $report['totals'] = [
            'hours' => round($totalHours, 3),
            'billable_hours' => round($totalBillableHours, 3),
            'salary' => $salaryTotals
        ];

        return $report;
    }

    /**
     * @param TimeLog $log
     * @param array $salaryTotals
     */
    private function sumSalary(TimeLog $log, array &$salaryTotals): void
    {
        $currencyCode = $log->currency()->code;
        if (isset($salaryTotals[$currencyCode])) {
            $salaryTotals[$currencyCode] += $log->salary();
            return;
        }

        $salaryTotals[$currencyCode] = $log->salary();
    }
}