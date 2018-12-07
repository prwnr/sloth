<?php

namespace Tests\Unit\Repository;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team\Member;
use App\Models\TimeLog;
use App\Repositories\TimeLogRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class TimeLogRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $timeLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timeLog = \Mockery::mock(TimeLog::class);
    }

    public function testFindsModel(): void
    {
        $expected = factory(TimeLog::class)->create();
        $expected->start = $expected->fromDateTime($expected->start);
        $repository = new TimeLogRepository(new TimeLog());

        $actual = $repository->find($expected->id);
        $this->assertInstanceOf(TimeLog::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(TimeLog::class)->create();
        $expected->start = $expected->fromDateTime($expected->start);
        $repository = new TimeLogRepository(new TimeLog());

        $expectedRelations = ['project', 'member'];
        $actual = $repository->findWith($expected->id, $expectedRelations);

        $this->assertInstanceOf(TimeLog::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        foreach ($expectedRelations as $expectedRelation) {
            $this->assertTrue($actual->relationLoaded($expectedRelation));
        }
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new TimeLogRepository(new TimeLog());
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new TimeLogRepository(new TimeLog());
        $repository->findWith(0, []);
    }

    public function testReturnCollection(): void
    {
        $expected = factory(TimeLog::class, 3)->create();

        $repository = new TimeLogRepository(new TimeLog());
        $actual = $repository->all();

        $expectedFirst = $expected->first();
        $expectedFirst->start = $expectedFirst->fromDateTime($expectedFirst->start);
        $this->assertEquals($expectedFirst->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithNoRelation(): void
    {
        $expected = factory(TimeLog::class, 3)->create();

        $repository = new TimeLogRepository(new TimeLog());
        $actual = $repository->allWith([]);

        $expectedFirst = $expected->first();
        $expectedFirst->start = $expectedFirst->fromDateTime($expectedFirst->start);
        $this->assertEquals($expectedFirst->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->getQueueableRelations());
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeTimeLogData();
        $repository = new TimeLogRepository(new TimeLog());

        $actual = $repository->create($expected);
        $expected['project_id'] = $expected['project'];
        $expected['task_id'] = $expected['task'];
        $expected['member_id'] = $expected['member'];
        unset($expected['project'], $expected['task'], $expected['member']);

        $this->assertInstanceOf(TimeLog::class, $actual);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(\ErrorException::class);
        $repository = new TimeLogRepository(new TimeLog());
        $repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(TimeLog::class)->create();
        $repository = new TimeLogRepository(new TimeLog());
        $expected = $this->makeTimeLogData();
        unset($expected['member'], $expected['duration']);

        $actual = $repository->update($model->id, $expected);
        $expected['project_id'] = $expected['project'];
        $expected['task_id'] = $expected['task'];
        unset($expected['project'], $expected['task']);

        $this->assertInstanceOf(TimeLog::class, $actual);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testStartsTimeLog(): void
    {
        $model = factory(TimeLog::class)->create();
        $expected = [
            'time' => TimeLog::START,
            'duration' => $this->faker->numberBetween(0, 99999),
        ];

        $repository = new TimeLogRepository(new TimeLog());
        $actual = $repository->updateTime($model->id, $expected);

        $this->assertInstanceOf(TimeLog::class, $actual);
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

        $repository = new TimeLogRepository(new TimeLog());
        $actual = $repository->updateTime($model->id, $expected);

        $this->assertInstanceOf(TimeLog::class, $actual);
        $this->assertEquals($expected['duration'], $actual->duration);
        $this->assertNull($actual->start);
    }

    public function testUpdatesDurationOfTimeLog(): void
    {
        $model = factory(TimeLog::class)->create();
        $expected = [
            'duration' => $this->faker->numberBetween(0, 99999),
        ];

        $repository = new TimeLogRepository(new TimeLog());
        $actual = $repository->updateTime($model->id, $expected);

        $this->assertInstanceOf(TimeLog::class, $actual);
        $this->assertEquals($expected['duration'], $actual->duration);
        $this->assertEquals($model->start, $actual->start);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $repository = new TimeLogRepository(new TimeLog());

        $this->expectException(ModelNotFoundException::class);
        $repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(TimeLog::class)->create();
        $repository = new TimeLogRepository(new TimeLog());

        $this->assertTrue($repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $expected = new TimeLog($this->makeTimeLogData());
        $this->timeLog->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new TimeLogRepository($this->timeLog);
        $this->assertFalse($repository->delete(1));
    }

    public function testThrowsModelNotFoundExceptionOnMOdelDeleteWithNotExistingModel(): void
    {
        $repository = new TimeLogRepository(new TimeLog());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
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
