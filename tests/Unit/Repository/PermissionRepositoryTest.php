<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Mockery\MockInterface;
use Tests\TestCase;

class PermissionRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $permission;

    public function setUp(): void
    {
        $this->permission = \Mockery::mock(Permission::class);
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $expected = new Permission($this->makePermissionData());
        $this->permission->shouldReceive('findOrFail')->once()->with(1, ['*'])->andReturn($expected);
        $repository = new PermissionRepository($this->permission);

        $this->assertEquals($expected, $repository->find(1));
    }

    public function testFindsModelByName(): void
    {
        $expected = new Permission($this->makePermissionData());
        $this->permission->shouldReceive('whereName->firstOrFail')
            ->with($expected->name)
            ->with(['*'])
            ->andReturn($expected);
        $repository = new PermissionRepository($this->permission);

        $this->assertEquals($expected, $repository->findByName(1));
    }

    public function testThrowsModelNotFoundExceptionOnFindModelByName(): void
    {
        $expected = new Permission($this->makePermissionData());
        $this->permission->shouldReceive('whereName->firstOrFail')
            ->with($expected->name)
            ->with(['*'])
            ->andThrow(ModelNotFoundException::class);

        $this->expectException(ModelNotFoundException::class);

        $repository = new PermissionRepository($this->permission);
        $repository->findByName(1);
    }


    public function testFindsModelWithNoRelation(): void
    {
        $expected = new Permission($this->makePermissionData());

        $this->permission->shouldReceive('with->findOrFail')->once()->with([])->with(1, ['*'])->andReturn($expected);

        $repository = new PermissionRepository($this->permission);
        $actual = $repository->findWith(1, []);

        $this->assertEquals($expected, $actual);
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->permission->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);
        $this->expectException(ModelNotFoundException::class);

        $repository = new PermissionRepository($this->permission);
        $repository->find(1);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->permission->shouldReceive('with->findOrFail')
            ->once()
            ->with([])
            ->with(1, ['*'])
            ->andThrowExceptions([new ModelNotFoundException()]);
        $this->expectException(ModelNotFoundException::class);

        $repository = new PermissionRepository($this->permission);
        $repository->findWith(1, []);
    }

    public function testReturnCollection(): void
    {
        $expected = new Collection([
            new Permission($this->makePermissionData()),
            new Permission($this->makePermissionData()),
            new Permission($this->makePermissionData())
        ]);

        $this->permission->shouldReceive('all')->with(['*'])->andReturn($expected);

        $repository = new PermissionRepository($this->permission);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithNoRelation(): void
    {
        $expected = new Collection([
            new Permission($this->makePermissionData()),
            new Permission($this->makePermissionData()),
            new Permission($this->makePermissionData())
        ]);

        $this->permission->shouldReceive('with->get')->with([])->with(['*'])->andReturn($expected);

        $repository = new PermissionRepository($this->permission);
        $actual = $repository->allWith([]);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->getQueueableRelations());
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makePermissionData();

        $repository = new PermissionRepository(new Permission());
        $actual = $repository->create($expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(QueryException::class);
        $repository = new PermissionRepository(new Permission());
        $repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = new Permission($this->makePermissionData());
        $model->exists = true;

        $this->permission->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($model);

        $repository = new PermissionRepository($this->permission);
        $expected = $this->makePermissionData();
        $actual = $repository->update(1, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->permission->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new PermissionRepository($this->permission);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $expected = new Permission($this->makePermissionData());
        $expected->exists = true;

        $this->permission->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new PermissionRepository($this->permission);

        $this->assertTrue($repository->delete(1));
    }

    public function testDoesNotDeleteModel(): void
    {
        $expected = new Permission($this->makePermissionData());

        $this->permission->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new PermissionRepository($this->permission);

        $this->assertFalse($repository->delete(1));
    }

    public function testThrowsModelNotFoundExceptionOnMOdelDeleteWithNotExistingModel(): void
    {
        $this->permission->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new PermissionRepository($this->permission);

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(1);
    }


    private function makePermissionData(): array
    {
        return [
            'name' => $this->faker->toLower($this->faker->word),
            'display_name' => $this->faker->word,
            'description' => $this->faker->sentence
        ];
    }
}
