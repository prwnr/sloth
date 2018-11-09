<?php

namespace Tests\Feature;


use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Team\Member;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackerTest extends FeatureTestCase
{

    public function testTrackerTimeLogsAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $data = [
            'member' => factory(Member::class)->create(['team_id' => $this->user->team_id])->id,
            'project' => factory(Project::class)->create(['team_id' => $this->user->team_id])->id,
            'description' => $this->faker->sentence,
            'duration' => $this->faker->numberBetween(1, 9999999),
            'created_at' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s')
        ];

        $response = $this->json(Request::METHOD_POST, '/api/time', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'member_id' => $data['member'],
                'project_id' => $data['project'],
                'task_id' => null,
                'description' => $data['description'],
                'created_at' => $data['created_at'],
                'start' => null,
                'project' => [],
                'member' => []
            ]
        ]);
    }

    public function testTrackerTimeLogsAreCreatedWithTasksCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $data = [
            'member' => factory(Member::class)->create(['team_id' => $this->user->team_id])->id,
            'project' => $project->id,
            'task' => factory(Task::class)->create(['project_id' => $project->id])->id,
            'description' => $this->faker->sentence,
            'duration' => $this->faker->numberBetween(1, 9999999),
            'created_at' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s')
        ];

        $response = $this->json(Request::METHOD_POST, '/api/time', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'member_id' => $data['member'],
                'project_id' => $data['project'],
                'task_id' => $data['task'],
                'description' => $data['description'],
                'created_at' => $data['created_at'],
                'start' => null,
                'project' => [],
                'task' => [],
                'member' => []
            ]
        ]);
    }

    public function testTrackerTimeLogsAreStartedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
        $data = [
            'member' => factory(Member::class)->create(['team_id' => $this->user->team_id])->id,
            'project' => $project->id,
            'task' => factory(Task::class)->create(['project_id' => $project->id])->id,
            'description' => $this->faker->sentence,
            'created_at' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s')
        ];

        $response = $this->json(Request::METHOD_POST, '/api/time', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'member_id' => $data['member'],
                'project_id' => $data['project'],
                'task_id' => $data['task'],
                'description' => $data['description'],
                'created_at' => $data['created_at'],
                'start' => [],
                'project' => [],
                'task' => [],
                'member' => []
            ]
        ]);
        $response->assertJsonStructure([
            'data' => [
                'start' => [
                    'date', 'timezone_type', 'timezone'
                ]
            ]
        ]);
    }

    public function testTrackerTimeLogsAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $log = factory(TimeLog::class)->create(['member_id' => $this->user->member()->id]);
        $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);

        $data = [
            'description' => $this->faker->sentence,
            'project' => $project->id,
            'task' => factory(Task::class)->create(['project_id' => $project])->id,
            'created_at' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s')
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/time/{$log->id}", $data);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'project_id' => $data['project'],
                'task_id' => $data['task'],
                'description' => $data['description'],
                'created_at' => $data['created_at']
            ]
        ]);
    }

    public function testTrackerTimeLogsAreRestartedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $log = factory(TimeLog::class)->create(['member_id' => $this->user->member()->id]);
        $data = [
            'duration' => $log->duration,
            'time' => 'start'
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/time/{$log->id}/duration", $data);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'start' => [
                    'date', 'timezone', 'timezone_type'
                ]
            ]
        ]);
    }

    public function testTrackerTimeLogsAreStoppedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $log = factory(TimeLog::class)->create(['member_id' => $this->user->member()->id]);
        $data = [
            'duration' => $log->duration,
            'time' => 'stop'
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/time/{$log->id}/duration", $data);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'start' => null
            ]
        ]);
    }

    public function testTrackerTimeLogsDurationIsUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $log = factory(TimeLog::class)->create(['member_id' => $this->user->member()->id, 'start' => null]);
        $data = [
            'duration' => $this->faker->numberBetween(1, 999999),
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/time/{$log->id}/duration", $data);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'start' => null,
                'duration' => $data['duration']
            ]
        ]);
    }

    public function testTrackerTimeLogsAreDeletedCorrectly()
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $log = factory(TimeLog::class)->create(['member_id' => $this->user->member()->id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/time/{$log->id}");
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
