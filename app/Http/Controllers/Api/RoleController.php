<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Repositories\RoleRepository;
use Illuminate\Http\{JsonResponse, Resources\Json\JsonResource, Resources\Json\ResourceCollection, Response};
use Illuminate\Support\Facades\DB;

/**
 * Class RoleController
 * @package App\Http\Controllers
 */
class RoleController extends Controller
{

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * RoleController constructor.
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Show list of all roles
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return new ResourceCollection($this->roleRepository->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoleRequest $request
     * @return JsonResponse
     */
    public function store(RoleRequest $request): JsonResponse
    {
        if ($this->roleRepository->findByName($request->input('name'))) {
            return response()->json(['message' => 'Role with this name already exists'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $role = DB::transaction(function () use ($request) {
                return $this->roleRepository->create($request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when creating new role. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new JsonResource($role))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResource
     */
    public function show(int $id): JsonResource
    {
        return new JsonResource($this->roleRepository->findWith($id, ['perms', 'members']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RoleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(RoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roleRepository->find($id);
        if (!$role->isEditable()) {
            return response()->json(['message' => 'You cannot edit this role'], Response::HTTP_BAD_REQUEST);
        }

        $similar = $this->roleRepository->findByName($request->input('name'));
        if ($similar && $similar->id !== $role->id) {
            return response()->json(['message' => 'Role with this name already exists'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $role = DB::transaction(function () use ($id, $request) {
                return $this->roleRepository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when updating role. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new JsonResource($role))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $role = $this->roleRepository->find($id);
        if (!$role->isDeletable()) {
            return response()->json(['message' => 'You can\'t delete this role'], Response::HTTP_FORBIDDEN);
        }

        try {
            $success = DB::transaction(function () use ($id) {
                return $this->roleRepository->delete($id);
            });

            if ($success) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            report($ex);
        }

        return response()->json([
            'message' => 'Something went wrong and role could not be deleted. It may not exists, please try again'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
