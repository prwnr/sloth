<?php

namespace App\Models\Report\Periodic;

use App\Models\Report\PeriodicReport;
use App\Models\TimeLog;

/**
 * Class UserProjects
 * @package App\Models\Report\Periodic
 */
class UserProjectsReport extends PeriodicReport
{

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $this->logs->selectRaw('project_id, sum(duration) as total_duration');
        $this->logs->whereNull('start');
        $this->logs->groupBy('project_id');
        $items = $this->gatherItems();

        $labels = [];
        $hours = [];
        foreach ($items as $item) {
            /** @var TimeLog $log */
            foreach ($item as $log) {
                $labels[] = $log->project->name;
                $hours[] = round($log->total_duration / 60, 3);
            }
        }

        return [
            'hours' => $hours,
            'labels' => $labels
        ];
    }
}