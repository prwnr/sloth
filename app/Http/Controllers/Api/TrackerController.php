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
        $this->validate($request, [
            'user' => 'required|numeric',
            'project' => 'required|numeric',
            'task' => $this->isTaskRequired($data) ? 'required|numeric' : 'nullable|numeric',
            'description' => 'nullable|string|max:200',
            'duration' => 'nullable|numeric'
        ]);

        $hasDuration = $data['duration'] ?? false;
        try {
            DB::beginTransaction();
            /** @var TimeLog $timeLog */
            $timeLog = TimeLog::create([
                'user_id' => $data['user'],
                'project_id' => $data['project'],
                'task_id' => $data['task'],
                'description' => $data['description'],
                'start' => $hasDuration ? null : Carbon::now(),
                'duration' => $hasDuration ? $data['duration'] : 0
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
        $data = $request->all();
        $this->validate($request, [
            'project' => 'required|numeric',
            'task' => $this->isTaskRequired($data) ? 'required|numeric' : 'nullable|numeric',
            'description' => 'nullable|string|max:200'
        ]);
        
        try {
            DB::beginTransaction();
            $time->update([
                'project_id' => $data['project'],
                'task_id' => $data['task'] ?? null,
                'description' => $data['description'],
            ]);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new TimeLogResource($time))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     * Updates only time for a log
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeLog  $time
     * @return \Illuminate\Http\Response
     */
    public function updateTime(Request $request, TimeLog $time)
    {
        $this->validate($request, [
            'duration' => 'required|numeric',
            'start' => 'nullable|date'
        ]);

        $data = $request->all();
        if (isset($data['time'])) {
            if ($data['time'] === TimeLog::STOP) {
                $data['start'] = null;
            }

            if ($data['time'] === TimeLog::START) {
                $data['start'] = Carbon::now();
            }

            unset($data['time']);
        }

        try {
            DB::beginTransaction();
            $time->update($data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong stopping time. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new TimeLogResource($time))->response()->setStatusCode(Response::HTTP_CREATED);
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

    /**
     * @param $data
     * @return bool
     */
    private function isTaskRequired($data): bool
    {
        if (isset($data['project'])) {
            /** @var Project $project */
            $project = Project::find($data['project']);
            if ($project) {
                return \count($project->tasks) > 0;
            }
        }

        return false;
    }
}
