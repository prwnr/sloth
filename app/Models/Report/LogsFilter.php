<?php

namespace App\Models\Report;

use App\Models\Date\DateRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LogsFilter
 * @package App\Models\Report
 */
class LogsFilter
{

    private const STATUS_FINISHED = 1;
    private const STATUS_IN_PROGRESS = 2;
    private const STATUS_ALL = 3;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var DateRange
     */
    private $range;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * LogsFilter constructor.
     * @param Builder $logsBuilder
     */
    public function __construct(Builder $logsBuilder)
    {
        $this->builder = $logsBuilder;
    }

    /**
     * @param $filter
     * @param $argument
     * @throws ReportFilterNotExists
     */
    public function __call($filter, $argument)
    {
        $filterName = ucfirst($filter);
        $filterMethod = "apply{$filterName}";
        if (!method_exists($this, $filterMethod)) {
            throw new ReportFilterNotExists("Attempted to use unknown filter: $filter");
        }

        $this->$filterMethod(array_pop($argument));
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Apply all filters
     */
    public function applyOptions(): void
    {
        foreach ($this->options as $field => $value) {
            if (empty($value)) {
                continue;
            }

            $this->$field();
        }
    }

    /**
     * Apply range filter (initialize it first)
     */
    public function applyRange(): void
    {
        $this->initRange();
        $this->builder->whereBetween('created_at', [
            $this->range->start(), $this->range->end()
        ]);
    }

    /**
     * Apply members filter
     */
    public function applyMembers(): void
    {
        $this->builder->whereIn('user_id', $this->options['members'] ?? []);
    }

    /**
     * Apply projects filter
     */
    public function applyProjects(): void
    {
        $this->builder->whereIn('project_id', $this->options['projects'] ?? []);
    }

    /**
     * Apply clients filter
     */
    public function applyClients(): void
    {
        $clients = $this->options['clients'] ?? [];
        $this->builder->whereHas('project', function ($query) use ($clients) {
            $query->whereIn('client_id', $clients);
        });
    }

    /**
     * Apply billable filter
     */
    public function applyBillable(): void
    {
        $billable = [];
        foreach ($this->options['billable'] as $billing) {
            $billable[] = $billing == 'yes' ? 1 : 0;
        }

        $this->builder->where(function ($query) use ($billable) {
            if (\in_array(1, $billable, true)) {
                $query->whereNull('task_id');
            }

            $query->orWhereHas('task', function ($query) use ($billable) {
                $query->whereIn('billable', $billable);
            });
        });
    }

    /**
     * Apply status filter
     */
    public function applyStatus(): void
    {
        if ((int)$this->options['status'] === self::STATUS_ALL) {
            return;
        }

        if ((int)$this->options['status'] === self::STATUS_FINISHED) {
            $this->builder->whereNull('start');
            return;
        }

        if ((int)$this->options['status'] === self::STATUS_IN_PROGRESS) {
            $this->builder->whereNotNull('start');
            return;
        }
    }

    /**
     * Initialize DateRange filter
     */
    private function initRange(): void
    {
        $range = $this->options['range'] ?? null;

        if (!$range) {
            $range = 'week';
        }

        if (\is_array($range)) {
            $start = Carbon::createFromFormat(DateRange::FORMAT, $range['start']);
            $end = Carbon::createFromFormat(DateRange::FORMAT, $range['end']);
            $this->range = new DateRange(DateRange::CUSTOM, $start, $end);
            return;
        }

        $this->range = new DateRange($range, Carbon::now());
    }
}