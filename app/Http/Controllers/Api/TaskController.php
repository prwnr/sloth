<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;

/**
 * Class TaskController
 * @package App\Http\Controllers\Api
 */
class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $tasks = [];
        foreach (Task::getTypes() as $type => $name) {
            $tasks[] = [
                'type' => $type,
                'name' => $name,
                'billable' => true
            ];
        }
        return $tasks;
    }
}
