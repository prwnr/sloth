<?php

namespace App\Models\Report;

use App\Models\Date\DateRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;


/**
 * Class Filters
 * @package App\Models\Report
 */
class Filters
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
    public function addOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Apply all filters
     * @param Builder $builder
     */
    public function applyAll(Builder $builder): void
    {
        $this->applyRange($builder);
        foreach ($this->options as $field => $value) {
            if (empty($value)) {
                continue;
            }

            $this->$field($builder);
        }
    }

    /**
     * @param Builder $builder
     */
    public function applyRange(Builder $builder): void
    {
        $this->initRange();
        $builder->whereBetween('created_at', [
            $this->range->start(), $this->range->end()
        ]);
    }

    /**
     * @param Builder $builder
     */
    public function applyMembers(Builder $builder): void
    {
        $builder->whereIn('user_id', $this->options['members'] ?? []);
    }

    /**
     * @param Builder $builder
     */
    public function applyProjects(Builder $builder): void
    {
        $builder->whereIn('project_id', $this->options['projects'] ?? []);
    }

    /**
     * @param Builder $builder
     */
    public function applyClients(Builder $builder): void
    {
        $clients = $this->options['clients'] ?? [];
        $builder->whereHas('project', function ($query) use ($clients) {
            $query->whereIn('client_id', $clients);
        });
    }

    /**
     * @param Builder $builder
     */
    public function applyBillable(Builder $builder): void
    {
        $billable = [];
        foreach ($this->options['billable'] as $billing) {
            $billable[] = $billing == 'yes' ? 1 : 0;
        }

        $builder->where(function ($query) use ($billable) {
            if (\in_array(1, $billable, true)) {
                $query->whereNull('task_id');
            }

            $query->orWhereHas('task', function ($query) use ($billable) {
                $query->whereIn('billable', $billable);
            });
        });
    }

    /**
     * @param Builder $builder
     */
    public function applyStatus(Builder $builder): void
    {
        if ((int)$this->options['status'] === self::STATUS_ALL) {
            return;
        }

        if ((int)$this->options['status'] === self::STATUS_FINISHED) {
            $builder->whereNull('start');
            return;
        }

        if ((int)$this->options['status'] === self::STATUS_IN_PROGRESS) {
            $builder->whereNotNull('start');
            return;
        }
    }

    /**
     * Initialize DateRange filter
     */
    private function initRange(): void
    {
        $range = $this->options['range'] ?? null;
        unset($this->options['range']);

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