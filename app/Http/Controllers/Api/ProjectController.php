<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProjectRequest;
use App\Models\{Report\ProjectReport, Project};
use App\Repositories\ProjectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\{JsonResource, ResourceCollection};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * Class ProjectController
 * @package App\Http\Controllers\Api
 */
class ProjectController extends Controller
{

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * ProjectController constructor.
     * @param ProjectRepository $projectRepository
     */
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return new ResourceCollection($this->projectRepository->allWith(['tasks']));
    }

    /**
     * Return available budget periods
     * @return array
     */
    public function budgetPeriods(): array
    {
        return Project::BUDGET_PERIOD;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectRequest $request
     * @return JsonResponse
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        try {
            $project = DB::transaction(function () use ($request) {
                return $this->projectRepository->create($request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when creating new project. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new JsonResource($project))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResource
     */
    public function show(int $id): JsonResource
    {
        $project = $this->projectRepository->findWith($id, ['members', 'client', 'billing', 'tasks', 'budgetCurrency', 'tasks.currency', 'billing.currency']);
        $projectResource = new JsonResource($project);

        $report = new ProjectReport(['projects' => [$project->id]]);

        $projectResource->additional([
            'report' => $report->generate()
        ]);
        return $projectResource;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProjectRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ProjectRequest $request, int $id): JsonResponse
    {
        try {
            $project = DB::transaction(function () use ($id, $request) {
                return $this->projectRepository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when updating project. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new JsonResource($project))->response()->setStatusCode(Response::HTTP_ACCEPTED);
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
                return $this->projectRepository->delete($id);
            });

            if ($success) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            report($ex);
        }

        return response()->json([
            'message' => 'Something went wrong and project could not be deleted. It may not exists, please try again'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
