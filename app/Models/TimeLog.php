<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TimeLog
 * @package App\Models
 */
class TimeLog extends Model
{
    public const STOP = 'stop';
    public const START = 'start';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'project_id', 'task_id', 'description', 'start', 'duration', 'created_at'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return float
     */
    public function salary(): float
    {
        if (!$this->isBillabe()) {
            return 0.0;
        }

        return $this->hours() * $this->payRate();
    }

    /**
     * Get log rate by task or a project
     * @return float
     */
    public function payRate(): float
    {
        if ($this->isBillabe() && $this->task) {
            return (float)$this->task->billing_rate;
        }

        return (float)$this->project->billing->rate;
    }

    /**
     * @return Currency
     */
    public function currency(): Currency
    {
        if ($this->isBillabe() && $this->task) {
            return $this->task->currency;
        }

        $this->loadMissing('project', 'project.billing', 'project.billing.currency');
        return $this->project->billing->currency;
    }

    /**
     * @return float
     */
    public function hours(): float
    {
        return round($this->duration / 60, 3);
    }

    /**
     * @return bool
     */
    public function isBillabe(): bool
    {
        if ($this->task && !$this->task->billable) {
            return false;
        }

        return true;
    }
}
