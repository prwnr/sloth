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

    /**
     * @var UserRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp(); 
        $this->repository = new UserRepository(new User());
    }

    public function testFindsModel(): void
    {
        $expected = factory(User::class)->create();
        $actual = $this->repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(User::class)->create();
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
        $this->repository->findWith(0, []);
    }

    public function testReturnsCollection(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(User::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithRelation(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(User::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->allWith(['team']);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('team'));
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeUserData();
        $actual = $this->repository->create($expected);
        unset($expected['password']);
        $expected['fullname'] = $expected['firstname'] . ' ' . $expected['lastname'];

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(QueryException::class);
        $this->repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(User::class)->create();
        $expected = $this->makeUserData();

        $actual = $this->repository->update($model->id, $expected);
        unset($expected['password']);
        $expected['fullname'] = $expected['firstname'] . ' ' . $expected['lastname'];

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(User::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $this->assertFalse($this->repository->delete(0));
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
