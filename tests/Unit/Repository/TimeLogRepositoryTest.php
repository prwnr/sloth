<?php

namespace Tests\Unit\Repository;

use App\Models\Project;
use App\Models\Project\Task;
use App\Models\Team\Member;
use App\Models\TimeLog;
use App\Repositories\TimeLogRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class TimeLogRepositoryTest extends TestCase
{

    /**
     * @var TimeLogRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TimeLogRepository(new TimeLog());
    }

    public function testFindsModel(): void
    {
        $expected = factory(TimeLog::class)->create();
        $expected->start = $expected->fromDateTime($expected->start);

        $actual = $this->repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(TimeLog::class)->create();
        $expected->start = $expected->fromDateTime($expected->start);

        $expectedRelations = ['project', 'member'];
        $actual = $this->repository->findWith($expected->id, $expectedRelations);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        foreach ($expectedRelations as $expectedRelation) {
            $this->assertTrue($actual->relationLoaded($expectedRelation));
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
        $expected = factory(TimeLog::class, 3)->create();
        $actual = $this->repository->all();

        $expectedFirst = $expected->first();
        $expectedFirst->start = $expectedFirst->fromDateTime($expectedFirst->start);
        $this->assertEquals($expectedFirst->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithNoRelation(): void
    {
        $expected = factory(TimeLog::class, 3)->create();
        $actual = $this->repository->allWith([]);

        $expectedFirst = $expected->first();
        $expectedFirst->start = $expectedFirst->fromDateTime($expectedFirst->start);
        $this->assertEquals($expectedFirst->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->getQueueableRelations());
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeTimeLogData();
        $actual = $this->repository->create($expected);

        $expected['project_id'] = $expected['project'];
        $expected['task_id'] = $expected['task'];
        $expected['member_id'] = $expected['member'];
        unset($expected['project'], $expected['task'], $expected['member']);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(\ErrorException::class);
        $this->repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(TimeLog::class)->create();
        $expected = $this->makeTimeLogData();
        unset($expected['member'], $expected['duration']);

        $actual = $this->repository->update($model->id, $expected);
        $expected['project_id'] = $expected['project'];
        $expected['task_id'] = $expected['task'];
        unset($expected['project'], $expected['task']);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testStartsTimeLog(): void
    {
        $model = factory(TimeLog::class)->create();
        $expected = [
            'time' => TimeLog::START,
            'duration' => $this->faker->numberBetween(0, 99999),
        ];

        $actual = $this->repository->updateTime($model->id, $expected);

        $this->assertEquals($expected['duration'], $actual->duration);
        $this->assertNotNull($actual->start);
    }

    public function testStopsTimeLog(): void
    {
        $model = factory(TimeLog::class)->create();
        $expected = [
            'time' => TimeLog::STOP,
            'duration' => $this->faker->numberBetween(0, 99999),
        ];

        $actual = $this->repository->updateTime($model->id, $expected);

        $this->assertEquals($expected['duration'], $actual->duration);
        $this->assertNull($actual->start);
    }

    public function testUpdatesDurationOfTimeLog(): void
    {
        $model = factory(TimeLog::class)->create();
        $expected = [
            'duration' => $this->faker->numberBetween(0, 99999),
        ];

        $actual = $this->repository->updateTime($model->id, $expected);

        $this->assertEquals($expected['duration'], $actual->duration);
        $this->assertEquals($model->start, $actual->start);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(TimeLog::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $this->assertFalse($this->repository->delete(1));
    }

    private function makeTimeLogData(): array
    {
        return [
            'project' => factory(Project::class)->create()->id,
            'task' => factory(Task::class)->create()->id,
            'member' => factory(Member::class)->create()->id,
            'description' => $this->faker->sentence,
            'start' => null,
            'duration' => $this->faker->numberBetween(0, 99999),
            'created_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
