<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\TimeLog;
use App\Http\Resources\TimeLog as TimeLogResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class TrackerController
 * @package App\Http\Controllers\Api
 */
class TrackerController extends Controller
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
        $data = $request->all();
        $taskRequired = false;
        if (isset($data['project'])) {
            /** @var Project $project */
            $project = Project::find($data['project']);
            if ($project) {
                $taskRequired = \count($project->tasks) > 0;
            }
        }

        $this->validate($request, [
            'user' => 'required|numeric',
            'project' => 'required|numeric',
            'task' => $taskRequired ? 'required|numeric' : 'nullable|numeric',
            'description' => 'nullable|string|max:200'
        ]);

        try {
            DB::beginTransaction();
            /** @var TimeLog $timeLog */
            $timeLog = TimeLog::create([
                'user_id' => $data['user'],
                'project_id' => $data['project'],
                'task_id' => $data['task'],
                'description' => $data['description'],
                'start' => Carbon::now(),
                'length' => 0
            ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['message' => __('Something went wrong when creating new time log. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        $timeLog->loadMissing('project', 'task', 'user');
        return (new TimeLogResource($timeLog))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeLog  $timeLog
     * @return \Illuminate\Http\Response
     */
    public function show(TimeLog $time)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeLog  $time
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TimeLog $time)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeLog  $time
     * @return \Illuminate\Http\Response
     */
    public function destroy(TimeLog $time)
    {
        DB::beginTransaction();
        try {
            if ($time->delete()) {
                DB::commit();
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('Something went wrong and your time log could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }
}
