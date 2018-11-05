<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Tracker\{CreateLogRequest, UpdateLogRequest, UpdateTimeRequest};
use App\Models\TimeLog;
use App\Http\Resources\TimeLog as TimeLogResource;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class TrackerController
 * @package App\Http\Controllers\Api
 */
class TrackerController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateLogRequest $request
     * @return JsonResponse
     */
    public function store(CreateLogRequest $request): JsonResponse
    {
        $data = $request->all();
        $hasDuration = $data['duration'] ?? false;

        try {
            DB::beginTransaction();
            /** @var TimeLog $timeLog */
            $timeLog = TimeLog::create([
                'member_id' => $data['member'],
                'project_id' => $data['project'],
                'task_id' => $data['task'] ?? null,
                'description' => $data['description'],
                'start' => $hasDuration ? null : Carbon::now(),
                'duration' => $hasDuration ? $data['duration'] : 0,
                'created_at' => $data['created_at']
            ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong when creating new time log. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        $timeLog->loadMissing('project', 'task', 'member');
        return (new TimeLogResource($timeLog))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLogRequest $request
     * @param  \App\Models\TimeLog $time
     * @return JsonResponse
     */
    public function update(UpdateLogRequest $request, TimeLog $time): JsonResponse
    {
        $data = $request->all();
        try {
            DB::beginTransaction();
            $time->update([
                'project_id' => $data['project'],
                'task_id' => $data['task'] ?? null,
                'description' => $data['description'],
                'created_at' => $data['created_at']
            ]);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new TimeLogResource($time))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Update the specified resource in storage.
     * Updates only time for a log
     *
     * @param UpdateTimeRequest $request
     * @param  \App\Models\TimeLog $time
     * @return JsonResponse
     */
    public function updateTime(UpdateTimeRequest $request, TimeLog $time): JsonResponse
    {
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

        return (new TimeLogResource($time))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeLog $time
     * @return JsonResponse
     */
    public function destroy(TimeLog $time): JsonResponse
    {
        try {
            DB::beginTransaction();
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
