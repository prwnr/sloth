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
}
