<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Role;
use App\Models\Project\Task;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectTest extends FeatureTestCase
{
    public function testProjectsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(Project::class, 5)->create(['team_id' => $this->user->team_id]);

        $response = $this->json(Request::METHOD_GET, '/api/projects');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['data' => [
            [
                'id', 'code', 'name', 'budget', 'budget_period', 'budget_currency_id', 'client_id', 'team_id', 'billing_id', 'created_at', 'updated_at', 'tasks'
            ]
        ]]);
        $response->assertJsonCount(5, 'data');
    }

    public function testProjectsAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/projects');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testProjectBudgetPeriodsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->json(Request::METHOD_GET, '/api/projects/budget_periods');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'month' => 'Month',
            'quarter' => 'Quarter',
            'year' => 'Year',
            'unlimited' => 'Unlimited'
        ]);
    }

    public function testProjectBudgetPeriodsAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/projects/budget_periods');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testProjectsAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $response = $this->json(Request::METHOD_POST, '/api/projects', $this->makeProjectData());

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'code', 'name', 'budget', 'budget_period', 'budget_currency_id', 'client_id', 'team_id', 'billing_id', 'created_at', 'updated_at'
            ]
        ]);
    }

    public function testProjectsAreNotCreatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $response = $this->json(Request::METHOD_POST, '/api/projects', $this->makeProjectData());

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testProjectsAreCreatedWithTasksCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $response = $this->json(Request::METHOD_POST, '/api/projects', $this->makeProjectData(true));

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'code', 'name', 'budget', 'budget_period', 'budget_currency_id', 'client_id', 'team_id', 'billing_id', 'created_at', 'updated_at'
            ]
        ]);
    }

    public function testProjectsAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_GET, "/api/projects/{$project->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'budget' => $project->budget,
                'budget_period' => $project->budget_period,
                'budget_currency_id' => $project->budget_currency_id,
                'client_id' => $project->client_id,
                'team_id' => $project->team_id,
                'billing_id' => $project->billing_id,
                'members' => [],
                'client' => [],
                'billing' => [],
                'tasks' => [],
                'budget_currency' => []
            ]
        ]);
    }

    public function testProjectsAreNotShowedForUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $project = factory(Project::class)->create(['team_id' => $differentTeam]);
        $response = $this->json(Request::METHOD_GET, "/api/projects/{$project->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testProjectsAreNotShowedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $project = factory(Project::class)->create();
        $response = $this->json(Request::METHOD_GET, "/api/projects/{$project->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testProjectsAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $data = $this->makeProjectData();
        $response = $this->json(Request::METHOD_PUT, "/api/projects/{$project->id}", $data);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'id' => $project->id,
                'code' => $data['code'],
                'name' => $data['name']
            ]
        ]);
    }

    public function testProjectsAreNotUpdatedByUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $project = factory(Project::class)->create(['team_id' => $differentTeam]);
        $response = $this->json(Request::METHOD_PUT, "/api/projects/{$project->id}", $this->makeProjectData());

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testProjectsAreNotUpdatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_PUT, "/api/projects/{$project->id}", $this->makeProjectData());

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testProjectsAreUpdatedWithTasksCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        /** @var Project $project */
        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $project->tasks()->save(factory(Task::class)->make());
        $data = $this->makeProjectData(true);
        $response = $this->json(Request::METHOD_PUT, "/api/projects/{$project->id}", $data);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'id' => $project->id,
                'code' => $data['code'],
                'name' => $data['name']
            ]
        ]);
    }

    public function testProjectsAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/projects/{$project->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testProjectsAreNotDeletedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create(['team_id' => $this->user->team_id]);
        $this->actAsRole($role->name);

        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/projects/{$project->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testProjectsAreNotDeletedForUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $project = factory(Project::class)->create(['team_id' => $differentTeam]);
        $response = $this->json(Request::METHOD_DELETE, "/api/projects/{$project->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @param bool $withTasks
     * @return array
     */
    private function makeProjectData($withTasks = false): array
    {
        $tasks = [];
        if ($withTasks) {
            foreach (Task::getTypes() as $type => $name) {
                $tasks[] = [
                    'name' => $name,
                    'type' => $type,
                    'billable' => true,
                    'currency' => Currency::all()->random()->id,
                    'is_deleted' => $this->faker->boolean
                ];
            }
        }

        return [
            'code' => $this->faker->toUpper($this->faker->randomLetter . $this->faker->randomLetter . $this->faker->randomLetter),
            'name' => $this->faker->company,
            'budget' => $this->faker->numberBetween(0, 999999),
            'budget_period' => $this->faker->randomKey(Project::BUDGET_PERIOD),
            'budget_currency' => Currency::all()->random()->id,
            'client' => factory(Client::class)->create(['team_id' => $this->user->team_id])->id,
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes()),
            'tasks' => $tasks
        ];
    }
}
