<?php

namespace App\Models;

use App\Models\Project\Task;
use App\Models\Team\Member;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TodoTask
 * @package App\Models
 */
class TodoTask extends Model
{

    public const PRIORITY_HIGH = 1;
    public const PRIORITY_MEDIUM = 2;
    public const PIORITY_LOW = 3;

    protected $fillable = [
        'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'priority'
    ];

    /**
     * @return BelongsTo
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
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
     * @return BelongsTo
     */
    public function timeLog(): BelongsTo
    {
        return $this->belongsTo(TimeLog::class);
    }
}
