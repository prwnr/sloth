<?php

namespace Tests\Unit\Repository;

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
        $expected = factory(Permission::class)->create();
        $repository = new PermissionRepository(new Permission());

        $actual = $repository->find($expected->id);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelByName(): void
    {
        $expected = factory(Permission::class)->create();
        $repository = new PermissionRepository(new Permission());

        $actual = $repository->findByName($expected->name);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFindModelByName(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new PermissionRepository(new Permission());
        $repository->findByName('foo');
    }

    public function testFindsModelWithNoRelation(): void
    {
        $expected = factory(Permission::class)->create();
        $repository = new PermissionRepository(new Permission());

        $actual = $repository->findWith($expected->id, []);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertEmpty($actual->relationsToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new PermissionRepository(new Permission());
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $repository = new PermissionRepository(new Permission());

         $repository->findWith(0, []);
    }

    public function testReturnCollection(): void
    {
        $expected = new Collection([
            new Permission($this->makePermissionData()),
            new Permission($this->makePermissionData()),
            new Permission($this->makePermissionData())
        ]);

        $this->permission->shouldReceive('query->get')
            ->withNoArgs()
            ->with(['*'])
            ->andReturn($expected);

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

        $this->permission->shouldReceive('query->with->get')
            ->withNoArgs()
            ->with([])
            ->with(['*'])
            ->andReturn($expected);

        $repository = new PermissionRepository($this->permission);
        $actual = $repository->allWith([]);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->first()->relationsToArray());
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
        $model = factory(Permission::class)->create();

        $repository = new PermissionRepository(new Permission());
        $expected = $this->makePermissionData();
        $actual = $repository->update($model->id, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $repository = new PermissionRepository(new Permission());

        $this->expectException(ModelNotFoundException::class);
        $repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Permission::class)->create();
        $repository = new PermissionRepository(new Permission());

        $this->assertTrue($repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $expected = new Permission($this->makePermissionData());
        $this->permission->shouldReceive('query->findOrFail')
            ->withNoArgs()
            ->with(1, ['*'])
            ->andReturn($expected);
        $repository = new PermissionRepository($this->permission);

        $this->assertFalse($repository->delete(1));
    }

    public function testThrowsModelNotFoundExceptionOnMOdelDeleteWithNotExistingModel(): void
    {
        $repository = new PermissionRepository(new Permission());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
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
