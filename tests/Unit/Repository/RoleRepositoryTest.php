<?php

namespace Tests\Unit\Repository;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Repositories\RoleRepository;
use ErrorException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery\MockInterface;
use Tests\TestCase;

class RoleRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $role;

    public function setUp(): void
    {
        $this->role = \Mockery::mock(Role::class);
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $expected = factory(Role::class)->create();
        $repository = new RoleRepository(new Role());

        $actual = $repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(Role::class)->create();
        $repository = new RoleRepository(new Role());
        $actual = $repository->findWith($expected->id, ['team']);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('team'));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $repository = new RoleRepository(new Role());

        $this->expectException(ModelNotFoundException::class);
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $repository = new RoleRepository(new Role());

        $this->expectException(ModelNotFoundException::class);
        $repository->findWith(0, ['team']);
    }

    public function testReturnCollection(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            new Role(),
            new Role(),
            new Role()
        ]);

        $this->role->shouldReceive('query->where->get')
            ->withNoArgs()
            ->with('team_id', $this->user->team_id)
            ->with(['*'])
            ->andReturn($expected);

        $repository = new RoleRepository($this->role);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            (new Role())->setRelation('team', factory(Team::class)->create()),
            (new Role())->setRelation('team', factory(Team::class)->create()),
            (new Role())->setRelation('team', factory(Team::class)->create())
        ]);

        $this->role->shouldReceive('query->where->with->get')
            ->withNoArgs()
            ->with('team_id', $this->user->team_id)
            ->with(['team'])
            ->with(['*'])
            ->andReturn($expected);

        $repository = new RoleRepository($this->role);
        $actual = $repository->allWith(['team']);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('team'));
    }

    public function testCreatesModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $expected = $this->makeRoleData();
        $repository = new RoleRepository(new Role());
        $actual = $repository->create($expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
        $this->assertEquals($user->team->id, $actual->team_id);
    }

    public function testCreatesModelWithMembersAndPermissions(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $expected = $this->makeRoleData();
        $data = $expected;
        $data['members'] = factory(Team\Member::class, 3)->create(['team_id' => $user->team->id])->pluck('id')->toArray();
        $data['permissions'] = Permission::all()->pluck('id')->toArray();

        $repository = new RoleRepository(new Role());
        $actual = $repository->create($data);

        $this->assertArraySubset($expected, $actual->attributesToArray());
        $this->assertEquals($user->team->id, $actual->team_id);
        $this->assertCount(3, $actual->members);
        $this->assertCount(\count($data['permissions']), $actual->perms);
    }

    public function testFailsToCreateModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $repository = new RoleRepository(new Role());

        $this->expectException(ErrorException::class);
        $repository->create([]);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Role::class)->create();
        $expected = $this->makeRoleData();
        $repository = new RoleRepository(new Role());

        $actual = $repository->update($model->id, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testUpdatesModelWithMembersAndPermissions(): void
    {
        $model = factory(Role::class)->create();
        $expected = $this->makeRoleData();
        $data = $expected;
        $data['members'] = factory(Team\Member::class, 3)->create()->pluck('id')->toArray();
        $data['permissions'] = Permission::all()->pluck('id')->toArray();

        $repository = new RoleRepository(new Role());
        $actual = $repository->update($model->id, $data);

        $this->assertArraySubset($expected, $actual->attributesToArray());
        $this->assertCount(3, $actual->members);
        $this->assertCount(\count($data['permissions']), $actual->perms);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->role->shouldReceive('query->findOrFail')
            ->withNoArgs()
            ->with(1, ['*'])
            ->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new RoleRepository($this->role);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Role::class)->create();
        $repository = new RoleRepository(new Role());

        $this->assertTrue($repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $model = new Role($this->makeRoleData());
        $this->role->shouldReceive('query->findOrFail')
            ->withNoArgs()
            ->with(1, ['*'])
            ->andReturn($model);

        $repository = new RoleRepository($this->role);

        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $repository = new RoleRepository(new Role());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
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