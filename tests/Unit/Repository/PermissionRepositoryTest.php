<?php

namespace Tests\Unit\Repository;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class PermissionRepositoryTest extends TestCase
{

    /**
     * @var PermissionRepository
     */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new PermissionRepository(new Permission());
        Permission::query()->delete(); // Permissions are pre-installed, for this repository tests we dont want them
    }

    public function testFindsModel(): void
    {
        $expected = factory(Permission::class)->create();
        $actual = $this->repository->find($expected->id);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelByName(): void
    {
        $expected = factory(Permission::class)->create();
        $actual = $this->repository->findByName($expected->name);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFindModelByName(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findByName('foo');
    }

    public function testFindsModelWithNoRelation(): void
    {
        $expected = factory(Permission::class)->create();
        $actual = $this->repository->findWith($expected->id, []);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertEmpty($actual->relationsToArray());
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
        $expected = factory(Permission::class, 3)->create();
        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithNoRelation(): void
    {
        $expected = factory(Permission::class, 3)->create();
        $actual = $this->repository->allWith([]);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->first()->relationsToArray());
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makePermissionData();
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
        $model = factory(Permission::class)->create();
        $expected = $this->makePermissionData();
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
        $model = factory(Permission::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $this->assertFalse($this->repository->delete(0));
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
