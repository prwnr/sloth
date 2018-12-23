<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team\Member;
use App\Models\TimeLog;
use App\Models\TodoTask;
use App\Repositories\TodoTaskRepository;
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
                    'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog', 'priority'
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
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog', 'priority'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnTodoTaskCreate(): void
    {
        $this->actingAs($this->user, 'api');
        $expecpted = $this->makeTodoTaskData();

        $mock = $this->mockAndReplaceInstance(TodoTaskRepository::class);
        $mock->shouldReceive('create')->with($expecpted)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_POST, '/api/todos', $expecpted);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong when creating new todo task. Please try again'
        ]);
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
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog', 'priority'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testTodoTasksAreUpdatedCorrectlyWithoutProjectTask(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);
        $expecpted = $this->makeTodoTaskData();
        unset($expecpted['task_id']);

        $response = $this->json(Request::METHOD_PUT, "/api/todos/{$todo->id}", $expecpted);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'member', 'project', 'task', 'timelog', 'priority'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnTodoTaskUpdate(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);
        $expecpted = $this->makeTodoTaskData();

        $mock = $this->mockAndReplaceInstance(TodoTaskRepository::class);
        $mock->shouldReceive('update')->with($todo->id, $expecpted)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/todos/{$todo->id}", $expecpted);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong when updating todo task. Please try again'
        ]);
    }

    public function testTodoTasksAreNotUpdatedByNotOwner(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create();
        $expecpted = $this->makeTodoTaskData();

        $response = $this->json(Request::METHOD_PUT, "/api/todos/{$todo->id}", $expecpted);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testTodoTasksStatusIsChangedToFinishedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);
        $expecpted = [
            'finished' => true
        ];

        $response = $this->json(Request::METHOD_PATCH, "/api/todos/{$todo->id}/status", $expecpted);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'priority'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnTodoTaskStatusChange(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);
        $expecpted = [
            'finished' => true
        ];

        $mock = $this->mockAndReplaceInstance(TodoTaskRepository::class);
        $mock->shouldReceive('update')->with($todo->id, $expecpted)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PATCH, "/api/todos/{$todo->id}/status", $expecpted);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong when changing todo task status. Please try again'
        ]);
    }

    public function testTodoTasksStatusIsChangedToUnfinishedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);
        $expecpted = [
            'finished' => false
        ];

        $response = $this->json(Request::METHOD_PATCH, "/api/todos/{$todo->id}/status", $expecpted);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'description', 'member_id', 'project_id', 'task_id', 'timelog_id', 'finished', 'priority'
            ]
        ]);
        $response->assertJsonFragment($expecpted);
    }

    public function testTodoTasksStatusIsNotChangedByNotOwner(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create();
        $expecpted = [
            'finished' => true
        ];

        $response = $this->json(Request::METHOD_PATCH, "/api/todos/{$todo->id}/status", $expecpted);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testTodoTasksAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);

        $response = $this->json(Request::METHOD_DELETE, "/api/todos/{$todo->id}" );
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }


    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnTodoTaskDelete(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);

        $mock = $this->mockAndReplaceInstance(TodoTaskRepository::class);
        $mock->shouldReceive('delete')->with($todo->id)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_DELETE, "/api/todos/{$todo->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong and todo task could not be deleted. It may not exists, please try again'
        ]);
    }

    public function testErrorMessageIsReturnedWhenTodoTaskCannotBeDeleted(): void
    {
        $this->actingAs($this->user, 'api');
        $todo = factory(TodoTask::class)->create(['member_id' => $this->member->id]);

        $mock = $this->mockAndReplaceInstance(TodoTaskRepository::class);
        $mock->shouldReceive('delete')->with($todo->id)->andReturn(false);

        $response = $this->json(Request::METHOD_DELETE, "/api/todos/{$todo->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong and todo task could not be deleted. It may not exists, please try again'
        ]);
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
