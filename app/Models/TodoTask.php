<?php

namespace App\Models;

use App\Models\Project\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TodoTask
 * @package App\Models
 */
class TodoTask extends Model
{
    protected $fillable = [
        'description', 'project_id', 'task_id', 'timelog_id', 'finished'
    ];

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
