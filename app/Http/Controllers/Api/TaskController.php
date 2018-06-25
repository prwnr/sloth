<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
