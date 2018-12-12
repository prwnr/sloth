<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Tracker\{CreateLogRequest, UpdateLogRequest, UpdateTimeRequest};
use App\Repositories\TimeLogRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class TrackerController
 * @package App\Http\Controllers\Api
 */
class TrackerController extends Controller
{

    /**
     * @var TimeLogRepository
     */
    private $timeLogRepository;

    /**
     * TrackerController constructor.
     * @param TimeLogRepository $timeLogRepository
     */
    public function __construct(TimeLogRepository $timeLogRepository)
    {
        $this->timeLogRepository = $timeLogRepository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateLogRequest $request
     * @return JsonResponse
     */
    public function store(CreateLogRequest $request): JsonResponse
    {
        try {
            $timeLog = DB::transaction(function () use ($request) {
                return $this->timeLogRepository->create($request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => __('Something went wrong when creating new time log. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        $timeLog->loadMissing('project', 'task', 'member');
        return (new JsonResource($timeLog))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLogRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateLogRequest $request, int $id): JsonResponse
    {
        try {
            $timeLog = DB::transaction(function () use ($id, $request) {
                return $this->timeLogRepository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => __('Something went wrong. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new JsonResource($timeLog))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Update the specified resource in storage.
     * Updates only time for a log
     *
     * @param UpdateTimeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateTime(UpdateTimeRequest $request, int $id): JsonResponse
    {
        try {
            $timeLog = DB::transaction(function () use ($id, $request) {
                return $this->timeLogRepository->updateTime($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => __('Something went wrong stopping time. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new JsonResource($timeLog))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = DB::transaction(function () use ($id) {
                return $this->timeLogRepository->delete($id);
            });

            if ($success) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('Something went wrong and your time log could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }
}
