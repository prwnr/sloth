<?php

namespace App\Models\Report;

use App\Models\Report;
use App\Models\TimeLog;

/**
 * Class ProjectReport
 * @package App\Models\Report
 */
class ProjectReport extends Report
{
    /**
     * @param Filters $filters
     */
    public function apply(Filters $filters): void
    {
        $filters->projects($this->logs);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $hours = 0;
        $billableHours = 0;
        $sale = 0;

        /** @var TimeLog $log */
        foreach ($this->logs->get() as $log) {
            $hours += $log->hours();
            $billableHours += $log->isBillabe() ? $log->hours() : 0.0;
            $sale += $log->salary();
        }

        $report = [
            'total_hours' => round($hours, 3),
            'total_billable_hours' => round($billableHours, 3),
            'total_sale' => round($sale, 2)
        ];

        return $report;
    }
}