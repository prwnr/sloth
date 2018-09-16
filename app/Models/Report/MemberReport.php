<?php

namespace App\Models\Report;

use App\Models\Report;
use App\Models\TimeLog;

/**
 * Class MemberReport
 * @package App\Models\Report
 */
class MemberReport extends Report
{

    /**
     * @param Filters $filters
     */
    public function apply(Filters $filters): void
    {
        $filters->members($this->logs);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $hours = 0;
        $billableHours = 0;
        $sale = 0;
        $earnings = 0;

        /** @var TimeLog $log */
        foreach ($this->logs->get() as $log) {
            $hours += $log->duration();
            $billableHours += $log->isBillabe() ? $log->duration() : 0.0;
            $sale += $log->memberSalary();
            $earnings += $log->salary();
        }

        $report = [
            'total_hours' => round($hours, 3),
            'total_billable_hours' => round($billableHours, 3),
            'total_sale' => round($sale, 2),
            'total_earnings' => round($earnings, 2)
        ];

        return $report;
    }
}