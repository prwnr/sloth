<?php

namespace Tests\Unit\Repository;

use App\Models\Team;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{

    public function testFindsModel(): void
    {
        $expected = factory(User::class)->create();
        $repository = new UserRepository(new User());

        $actual = $repository->find($expected->id);
        $this->assertInstanceOf(User::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(User::class)->create();
        $repository = new UserRepository(new User());

        $actual = $repository->findWith($expected->id, ['team']);

        $this->assertInstanceOf(User::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('team'));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new UserRepository(new User());
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new UserRepository(new User());
        $repository->findWith(0, []);
    }

    public function testReturnCollection(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team_id')->andReturn(factory(Team::class)->create()->id);
        $this->actingAs($user, 'api');
        $expected = factory(User::class, 3)->create(['team_id' => $user->team_id]);

        $repository = new UserRepository(new User());
        $actual = $repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithRelation(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team_id')->andReturn(factory(Team::class)->create()->id);
        $this->actingAs($user, 'api');
        $expected = factory(User::class, 3)->create(['team_id' => $user->team_id]);

        $repository = new UserRepository(new User());
        $actual = $repository->allWith(['team']);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('team'));
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeUserData();
        $repository = new UserRepository(new User());

        $actual = $repository->create($expected);
        unset($expected['password']);
        $expected['fullname'] = $expected['firstname'] . ' ' . $expected['lastname'];

        $this->assertInstanceOf(User::class, $actual);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(QueryException::class);
        $repository = new UserRepository(new User());
        $repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(User::class)->create();
        $repository = new UserRepository(new User());
        $expected = $this->makeUserData();

        $actual = $repository->update($model->id, $expected);
        unset($expected['password']);
        $expected['fullname'] = $expected['firstname'] . ' ' . $expected['lastname'];

        $this->assertInstanceOf(User::class, $actual);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $repository = new UserRepository(new User());

        $this->expectException(ModelNotFoundException::class);
        $repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(User::class)->create();
        $repository = new UserRepository(new User());

        $this->assertTrue($repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $expected = new User($this->makeUserData());
        $this->user->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new UserRepository($this->user);
        $this->assertFalse($repository->delete(1));
    }

    public function testThrowsModelNotFoundExceptionOnModelDeleteWithNotExistingModel(): void
    {
        $repository = new UserRepository(new User());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
    }

    /**
     * @return array
     */
    private function makeUserData(): array
    {
        $teamId = factory(Team::class)->create()->id;
        return [
            'firstname' => $this->faker->name,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'secret',
            'owns_team' => $teamId,
            'first_login' => false
        ];
    }
}
