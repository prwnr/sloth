<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TodoTask
 * @package App\Models
 */
class TodoTask extends Model
{
    protected $fillable = [
        'description', 'project_id', 'task_id', 'timelog_id', 'finished'
    ];
}
