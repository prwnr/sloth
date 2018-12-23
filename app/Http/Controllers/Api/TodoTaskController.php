<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TodoTaskAuthorizationRequest;
use App\Http\Requests\TodoTaskRequest;
use App\Repositories\TodoTaskRepository;
use Illuminate\Http\{JsonResponse, Resources\Json\JsonResource, Resources\Json\ResourceCollection, Response};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class TodoTaskController
 * @package App\Http\Controllers\Api
 */
class TodoTaskController extends Controller
{

    /**
     * @var TodoTaskRepository
     */
    private $repository;

    /**
     * TodoTaskController constructor.
     * @param TodoTaskRepository $todoTaskRepository
     */
    public function __construct(TodoTaskRepository $todoTaskRepository)
    {
        $this->repository = $todoTaskRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        $user = Auth::user();
        return new ResourceCollection($this->repository->allOfMemberWith($user->member()->id, ['project', 'task', 'timelog', 'member']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TodoTaskRequest $request
     * @return JsonResponse
     */
    public function store(TodoTaskRequest $request): JsonResponse
    {
        try {
            $todo = DB::transaction(function () use ($request) {
                return $this->repository->create($request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when creating new todo task. Please try again'], Response::HTTP_BAD_REQUEST);
        }

        $todo->loadMissing(['project', 'task', 'timelog', 'member']);
        return (new JsonResource($todo))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TodoTaskRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(TodoTaskRequest $request, int $id): JsonResponse
    {
        $data = $request->all();
        if (!isset($data['task_id']) || $data['task_id'] === 0) {
            $data['task_id'] = null;
        }

        try {
            $todo = DB::transaction(function () use ($id, $data) {
                return $this->repository->update($id, $data);
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when updating todo task. Please try again'], Response::HTTP_BAD_REQUEST);
        }

        $todo->loadMissing(['project', 'task', 'timelog', 'member']);
        return (new JsonResource($todo))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Change task status
     *
     * @param TodoTaskAuthorizationRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changeStatus(TodoTaskAuthorizationRequest $request, int $id): JsonResponse
    {
        $this->validate($request, ['finished' => ['required', 'boolean']]);

        try {
            $todo = DB::transaction(function () use ($id, $request) {
                return $this->repository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when changing todo task status. Please try again'], Response::HTTP_BAD_REQUEST);
        }

        return (new JsonResource($todo))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TodoTaskAuthorizationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(TodoTaskAuthorizationRequest $request, int $id): JsonResponse
    {
        try {
            $success = DB::transaction(function () use ($id) {
                return $this->repository->delete($id);
            });

            if ($success) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            report($ex);
        }

        return response()->json([
            'message' => 'Something went wrong and todo task could not be deleted. It may not exists, please try again'
        ], Response::HTTP_BAD_REQUEST);
    }
}
