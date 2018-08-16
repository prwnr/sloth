<?php

namespace App\Models\Date;

use Carbon\Carbon;

/**
 * Class DateRange
 * @package App\Models\Date
 */
class DateRange
{

    public const WEEK = 'week';
    public const MONTH = 'month';
    public const YEAR = 'year';
    public const CUSTOM = 'custom';
    public const FORMAT = 'Y-m-d';

    /**
     * @var string
     */
    private $type;

    /**
     * @var Carbon
     */
    private $start;

    /**
     * @var Carbon
     */
    private $end;

    /**
     * DateRange2 constructor.
     * @param string $type
     * @param Carbon $start
     * @param Carbon|null $end
     */
    public function __construct(string $type, Carbon $start, ?Carbon $end = null)
    {
        $this->type = $type;
        $this->start = $start;
        $this->end = $end ?? $start->copy();
        $this->initialize();
    }

    /**
     * @return Carbon
     */
    public function start(): Carbon
    {
        return $this->start;
    }

    /**
     * @return Carbon
     */
    public function end(): Carbon
    {
        return $this->end;
    }

    /**
     * Initiliaze start and end of date range based on type
     */
    private function initialize(): void
    {
        switch ($this->type) {
            case self::WEEK:
                $this->useWeek();
                return;
            case self::MONTH:
                $this->useMonth();
                return;
            case self::YEAR:
                $this->useYear();
                return;
            case self::CUSTOM:
                $this->useCustom();
                return;
            default:
                $this->useMonth();
        }
    }

    /**
     * Set start and end of week
     */
    private function useWeek(): void
    {
        $this->start->startOfWeek();
        $this->end->endOfWeek();
    }

    /**
     * Set start and end of month
     */
    private function useMonth(): void
    {
        $this->start->startOfMonth();
        $this->end->endOfMonth();
    }

    /**
     * Set start and end of year
     */
    private function useYear(): void
    {
        $this->start->startOfYear();
        $this->end->endOfYear();
    }

    /**
     * Set custom start and end of date
     */
    private function useCustom(): void
    {
        $this->start->startOfDay();
        $this->end->endOfDay();
    }
}