<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProjectRequest;
use App\Models\{Report\ProjectReport, Team, Client, Project};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Project as ProjectResource;

/**
 * Class ProjectController
 * @package App\Http\Controllers\Api
 */
class ProjectController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return ProjectResource
     */
    public function index()
    {
        $projects = Project::findFromTeam(Auth::user()->team)->get();
        $projects->loadMissing('tasks');
        return new ProjectResource($projects);
    }

    /**
     * Return available budget periods
     * @return array
     */
    public function budgetPeriods()
    {
        return Project::BUDGET_PERIOD;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectRequest $request)
    {
        $data = $request->all();

        /** @var Team $team */
        $team = Auth::user()->team;

        try {
            DB::beginTransaction();
            /** @var Project $project */
            $project = $team->projects()->create([
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'budget' => $data['budget'],
                'budget_period' => $data['budget_period'],
                'budget_currency_id' => $data['budget_currency'],
            ]);

            $this->createBilling($project, $data);
            $this->createTasks($project, $data);
            $client = Client::findOrFail($data['client']);
            $project->client()->associate($client);
            $project->members()->sync($data['members'] ?? []);

            $project->save();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong when creating new project. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new ProjectResource($project))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project $project
     * @return ProjectResource
     */
    public function show(Project $project)
    {
        $project->loadMissing('members', 'client', 'billing', 'tasks', 'budgetCurrency', 'tasks.currency', 'billing.currency');
        $projectResource = new ProjectResource($project);

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
     * @param  \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            /** @var Project $project */
            $project->update([
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'budget' => $data['budget'],
                'budget_period' => $data['budget_period'],
                'budget_currency_id' => $data['budget_currency'],
            ]);

            $this->updateBilling($project, $data);
            $this->updateTasks($project, $data);
            $client = Client::findOrFail($data['client']);
            $project->client()->associate($client);
            $project->members()->sync($data['members'] ?? []);

            $project->save();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong when updating project. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new ProjectResource($project))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        DB::beginTransaction();
        try {
            $project->members()->sync([]);
            if ($project->delete()) {
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
            'message' => __('Something went wrong and project could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Project $project
     * @param array $data
     */
    private function createBilling(Project $project, array $data): void
    {
        $billing = $project->billing()->create([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);

        $project->billing()->associate($billing);
    }

    /**
     * @param Project $project
     * @param array $data
     */
    private function createTasks(Project $project, $data): void
    {
        foreach ((array)$data['tasks'] as $task) {
            $billingRate = 0.00;
            $currency = null;
            if ((bool) $task['billable'] === true) {
                $billingRate = $task['billing_rate'] ?? $data['billing_rate'];
                $currency = $task['currency'] === 0 ? $data['billing_currency'] : $task['currency'];
            }

            $project->tasks()->create([
                'type' => $task['type'],
                'name' => $task['name'],
                'billable' => (bool) $task['billable'],
                'billing_rate' => (float) $billingRate,
                'currency_id' => $currency
            ]);
        }
    }

    /**
     * @param Project $project
     * @param array $data
     */
    private function updateBilling(Project $project, array $data): void
    {
        $project->billing()->update([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
    }

    /**
     * @param Project $project
     * @param array $data
     */
    private function updateTasks(Project $project, array $data): void
    {
        foreach ((array)$data['tasks'] as $task) {
            $billingRate = 0.00;
            $currency = null;
            if ((bool)$task['billable'] === true) {
                $billingRate = $task['billing_rate'] ?? $data['billing_rate'];
                $currency = $task['currency'] === 0 ? $data['billing_currency'] : $task['currency'];
            }

            $taskData = [
                'type' => $task['type'],
                'name' => $task['name'],
                'billable' => (bool)$task['billable'],
                'billing_rate' => (float) $billingRate,
                'currency_id' => $currency,
                'is_deleted' => (float) $task['is_deleted']
            ];

            $taskId = $task['id'] ?? 0;
            $project->tasks()->updateOrCreate(['id' => $taskId], $taskData);
        }
    }
}
