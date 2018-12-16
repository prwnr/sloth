<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TodoTaskRequest;
use App\Repositories\TodoTaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            return response()->json(['message' => __('Something went wrong when creating new todo task. Please try again')], Response::HTTP_BAD_REQUEST);
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
        $todo = $this->repository->find($id);
        if ($todo->member->id !== Auth::user()->member()->id) {
            return response()->json(['message' => 'You are not allowed to edit this todo task'], Response::HTTP_FORBIDDEN);
        }

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
            return response()->json(['message' => __('Failed to update todo task. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        $todo->loadMissing(['project', 'task', 'timelog', 'member']);
        return (new JsonResource($todo))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Change task status
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $todo = $this->repository->find($id);
        if ($todo->member->id !== Auth::user()->member()->id) {
            return response()->json(['message' => 'You are not allowed to edit this todo task'], Response::HTTP_FORBIDDEN);
        }

        $this->validate($request, ['finished' => ['required', 'boolean']]);

        try {
            $todo = DB::transaction(function () use ($id, $request) {
                return $this->repository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => __('Failed to update todo task. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new JsonResource($todo))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $todo = $this->repository->find($id);
        if ($todo->member->id !== Auth::user()->member()->id) {
            return response()->json(['message' => 'You are not allowed to delete this todo task'], Response::HTTP_FORBIDDEN);
        }

        try {
            $success = DB::transaction(function () use ($id) {
                return $this->repository->delete($id);
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
            'message' => __('Something went wrong and member could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }
}
