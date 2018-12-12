<?php

namespace App\Http\Controllers\Api;

use App\Models\TodoTask;
use Illuminate\Http\Request;

class TodoTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TodoTask  $todoTask
     * @return \Illuminate\Http\Response
     */
    public function show(TodoTask $todoTask)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TodoTask  $todoTask
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TodoTask $todoTask)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TodoTask  $todoTask
     * @return \Illuminate\Http\Response
     */
    public function destroy(TodoTask $todoTask)
    {
        //
    }
}
