<?php

namespace Tests\Unit\Repository;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Repositories\RoleRepository;
use ErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class RoleRepositoryTest extends TestCase
{

    /**
     * @var RoleRepository
     */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleRepository(new Role());
    }

    public function testFindsModel(): void
    {
        $expected = factory(Role::class)->create();
        $actual = $this->repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(Role::class)->create();
        $actual = $this->repository->findWith($expected->id, ['team']);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('team'));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findWith(0, ['team']);
    }

    public function testReturnsCollection(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Role::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Role::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->allWith(['team']);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('team'));
    }

    public function testCreatesModel(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = $this->makeRoleData();
        $actual = $this->repository->create($expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
        $this->assertEquals($this->user->team->id, $actual->team_id);
    }

    public function testCreatesModelWithMembersAndPermissions(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = $this->makeRoleData();
        $data = $expected;
        $data['members'] = factory(Team\Member::class, 3)->create(['team_id' => $this->user->team->id])->pluck('id')->toArray();
        $data['permissions'] = Permission::all()->pluck('id')->toArray();

        $actual = $this->repository->create($data);

        $this->assertArraySubset($expected, $actual->attributesToArray());
        $this->assertEquals($this->user->team->id, $actual->team_id);
        $this->assertCount(3, $actual->members);
        $this->assertCount(\count($data['permissions']), $actual->perms);
    }

    public function testFailsToCreateModel(): void
    {
        $this->actingAs($this->user, 'api');

        $this->expectException(ErrorException::class);
        $this->repository->create([]);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Role::class)->create();
        $expected = $this->makeRoleData();

        $actual = $this->repository->update($model->id, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testUpdatesModelWithMembersAndPermissions(): void
    {
        $model = factory(Role::class)->create();
        $expected = $this->makeRoleData();
        $data = $expected;
        $data['members'] = factory(Team\Member::class, 3)->create()->pluck('id')->toArray();
        $data['permissions'] = Permission::all()->pluck('id')->toArray();

        $actual = $this->repository->update($model->id, $data);

        $this->assertArraySubset($expected, $actual->attributesToArray());
        $this->assertCount(3, $actual->members);
        $this->assertCount(\count($data['permissions']), $actual->perms);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Role::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $this->assertFalse($this->repository->delete(0));
    }

    private function makeRoleData(): array
    {
        return [
            'name' => $this->faker->name,
            'display_name' => $this->faker->name,
            'description' => $this->faker->sentence
        ];
    }

}