<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProjectRepository
 * @package App\Repositories
 */
class ProjectRepository implements RepositoryInterface
{

    /**
     * @var Project
     */
    private $project;

    /**
     * ProjectRepository constructor.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->project->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->project->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     * @return Project
     */
    public function find(int $id, array $columns = ['*']): Project
    {
        return $this->project->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Project
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Project
    {
        return $this->project->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Project
     */
    public function create(array $data): Project
    {
        $team = Auth::user()->team;
        /** @var Project $project */
        $project = $team->projects()->create([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'budget' => $data['budget'],
            'budget_period' => $data['budget_period'],
            'budget_currency_id' => $data['budget_currency'],
        ]);

        $billing = $project->billing()->create([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
        $project->billing()->associate($billing);

        $this->createTasks($project, $data);

        $client = Client::findOrFail($data['client']);
        $project->client()->associate($client);
        $project->members()->sync($data['members'] ?? []);

        $project->save();

        return $project;
    }

    /**
     * {@inheritdoc}
     * @return Project
     */
    public function update(int $id, array $data): Project
    {
        $project = $this->find($id);
        $project->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'budget' => $data['budget'],
            'budget_period' => $data['budget_period'],
            'budget_currency_id' => $data['budget_currency'],
        ]);

        $project->billing()->update([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
        $this->updateTasks($project, $data);
        $client = Client::findOrFail($data['client']);
        $project->client()->associate($client);
        $project->members()->sync($data['members'] ?? []);

        $project->save();

        return $project;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $project = $this->find($id);
        $project->members()->sync([]);
        if ($project->delete()) {
            return true;
        }

        return false;
    }

    /**
     * @param Project $project
     * @param array $data
     */
    private function createTasks(Project $project, array $data): void
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