<?php

namespace Tests\Unit\Repository;

use App\Models\Project\Task;
use App\Models\TimeLog;
use App\Models\TodoTask;
use App\Repositories\TodoTaskRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class TodoTaskRepositoryTest extends TestCase
{
    /**
     * @var TodoTaskRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TodoTaskRepository(new TodoTask());
    }

    public function testFindsModel(): void
    {
        $expected = factory(TodoTask::class)->create();

        $actual = $this->repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(TodoTask::class)->create();

        $expectedRelations = ['project', 'task', 'timelog'];
        $actual = $this->repository->findWith($expected->id, $expectedRelations);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        foreach ($expectedRelations as $relation) {
            $this->assertTrue($actual->relationLoaded($relation));
        }
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findWith(0, []);
    }

    public function testReturnsCollection(): void
    {
        $expected = factory(TodoTask::class, 3)->create();
        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithRelation(): void
    {
        $expected = factory(TodoTask::class, 3)->create();
        $expectedRelations = ['project', 'task', 'timelog'];
        $actual = $this->repository->allWith($expectedRelations);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        foreach ($expectedRelations as $relation) {
            $this->assertTrue($actual->first()->relationLoaded($relation));
        }
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeTodoTaskData();

        $actual = $this->repository->create($expected);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(QueryException::class);
        $this->repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(TodoTask::class)->create();
        $expected = $this->makeTodoTaskData();
        $actual = $this->repository->update($model->id, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(TodoTask::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteNotExistingModel(): void
    {
        $this->assertFalse($this->repository->delete(0));
    }

    private function makeTodoTaskData()
    {
        $task = factory(Task::class)->create();
        return [
            'description' => $this->faker->sentence,
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'timelog_id' => factory(TimeLog::class)->create()->id,
            'finished' => $this->faker->boolean
        ];
    }
}
