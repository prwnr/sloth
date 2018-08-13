<?php


namespace App\Models\Date;

use Carbon\Carbon;

/**
 * Class DateRange
 * @package App\Models\Date
 */
abstract class DateRange
{

    public const FORMAT = 'Y-m-d';

    /**
     * @var Carbon
     */
    protected $start;

    /**
     * @var Carbon
     */
    protected $end;

    /**
     * @var string
     */
    protected $date;

    /**
     * DateRange constructor.
     * @param string $date
     */
    public function __construct(string $date)
    {
        $this->date = Carbon::createFromTimeString($date)->format(self::FORMAT);
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
     * Sets the Start and End of given range type
     */
    abstract protected function initialize(): void;
}