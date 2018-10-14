<?php

namespace App\Models\Report\Periodic;

use App\Models\Date\DateRange;
use App\Models\Report\PeriodicReport;
use App\Models\TimeLog;
use Carbon\Carbon;

/**
 * Class SalesReport
 * @package App\Models\Report\Periodic
 */
class SalesReport extends PeriodicReport
{

    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        $items = $this->groupItems();
        $sales = [];
        foreach ($items as $period => $item) {
            /** @var TimeLog $log */
            foreach ($item as $log) {
                $salary = $log->salary();
                $number = ltrim($period, '0');
                if ($this->period === DateRange::WEEK) {
                    $number = Carbon::createFromFormat('d', $number)->dayOfWeek;
                }

                $projectCurrencyId = $log->project_id . '-' . $log->currency()->id;
                if (!isset($sales[$projectCurrencyId])) {
                    $sales[$projectCurrencyId] = [];
                    $sales[$projectCurrencyId]['label'] = $log->project->name . "({$log->currency()->code})";
                    $sales[$projectCurrencyId]['data'] = $this->preparePeriodsArray();
                }

                $sales[$projectCurrencyId]['data'][$number] += round($salary, 2);
                $sales[$projectCurrencyId]['data'][$number] = round($sales[$projectCurrencyId]['data'][$number], 2);
            }
        }


        if ($this->period === DateRange::WEEK) {
            foreach ($sales as $key => $sale) {
                $sales[$key]['data'] = first_to_last($sales[$key]['data']);
            }
        }

        return [
            'sales' => $sales,
            'labels' => $this->makeLabels()
        ];
    }
}