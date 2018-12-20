<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team\Member;
use App\Models\TimeLog;
use App\Models\TodoTask;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoTaskTest extends FeatureTestCase
{

    public function testTodoTasksAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(TodoTask::class, 5)->create(['member_id' => $this->member->id]);

        $response = $this->json(Request::METHOD_GET, '/api/todos');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog'
                ]
            ]
        ]);
        $response->assertJsonCount(5, 'data');
    }

    public function testTodoTasksAreNotListedForDifferentMember(): void
    {
        $this->actingAs($this->user, 'api');
        factory(TodoTask::class, 5)->create(['member_id' => factory(Member::class)->create()->id]);

        $response = $this->json(Request::METHOD_GET, '/api/todos');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'data');
    }

    public function testTodoTasksAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $expecpted = $this->makeTodoTaskData();

        $response = $this->json(Request::METHOD_POST, '/api/todos', $expecpted);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testTodoTasksAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);
        $expecpted = $this->makeTodoTaskData();

        $response = $this->json(Request::METHOD_PUT, "/api/todos/{$todo->id}", $expecpted);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testTodoTasksAreNotUpdatedByNotOwner(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create();
        $expecpted = $this->makeTodoTaskData();

        $response = $this->json(Request::METHOD_PUT, "/api/todos/{$todo->id}", $expecpted);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testTodoTasksAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);

        $response = $this->json(Request::METHOD_DELETE, "/api/todos/{$todo->id}" );
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testTodoTasksAreNotDeletedByNotOwner(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create();

        $response = $this->json(Request::METHOD_DELETE, "/api/todos/{$todo->id}" );
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    private function makeTodoTaskData(): array
    {
        return [
            'description' => $this->faker->sentence,
            'member_id' => factory(Member::class)->create()->id,
            'project_id' => factory(Project::class)->create()->id,
            'task_id' => factory(Project\Task::class)->create()->id,
            'timelog_id' => $this->faker->randomElement([factory(TimeLog::class)->create()->id, null]),
            'finished' => $this->faker->boolean,
            'priority' => $this->faker->numberBetween(0, 5)
        ];
    }
}
