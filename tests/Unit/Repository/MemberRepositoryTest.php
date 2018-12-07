<?php

namespace Tests\Unit\Repository;

use App\Models\Billing;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\Team\Member;
use App\Models\User;
use App\Repositories\MemberRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Mockery\MockInterface;
use Tests\TestCase;


class MemberRepositoryTest extends TestCase
{

    /**
     * @var MockInterface
     */
    private $member;

    public function setUp(): void
    {
        $this->member = \Mockery::mock(Member::class);
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Member::class)->create();
        $repository = new MemberRepository(new Member());

        $actual = $repository->find($expected->id);

        $this->assertInstanceOf(Member::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Member::class)->create();

        $expectedRelations = ['user', 'billing', 'team'];

        $repository = new MemberRepository(new Member());
        $actual = $repository->findWith($expected->id, $expectedRelations);

        $this->assertInstanceOf(Member::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        foreach ($expectedRelations as $expectedRelation) {
            $this->assertTrue($actual->relationLoaded($expectedRelation));
        }
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $repository = new MemberRepository(new Member());

        $this->expectException(ModelNotFoundException::class);
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $repository = new MemberRepository(new Member());

        $this->expectException(ModelNotFoundException::class);
        $repository->findWith(0, ['billing', 'user', 'team']);
    }

    public function testReturnCollection(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            new Member(),
            new Member(),
            new Member()
        ]);

        $this->member->shouldReceive('where->get')
            ->with('team_id', $this->user->team_id)
            ->with(['*'])
            ->andReturn($expected);

        $repository = new MemberRepository($this->member);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            (new Member())->setRelations([
                'billing' => factory(Billing::class)->create(),
                'user' => factory(User::class)->create(),
                'team' => factory(Team::class)->create(),
            ]),
            (new Member())->setRelations([
                'billing' => factory(Billing::class)->create(),
                'user' => factory(User::class)->create(),
                'team' => factory(Team::class)->create(),
            ])
        ]);

        $expectedRelations = ['billing', 'user'];
        $this->member->shouldReceive('where->with->get')
            ->with('team_id', $this->user->team_id)
            ->with($expectedRelations)
            ->with(['*'])
            ->andReturn($expected);

        $repository = new MemberRepository($this->member);
        $actual = $repository->allWith($expectedRelations);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(2, $actual->count());
        foreach ($expectedRelations as $expectedRelation) {
            $this->assertTrue($actual->first()->relationLoaded($expectedRelation));
        }
    }

    public function testCreatesModel(): void
    {
        $user = \Mockery::mock(User::class);
        $team = factory(Team::class)->create();
        $user->shouldReceive('getAttribute')->with('team')->andReturn($team);
        $user->shouldReceive('getAttribute')->with('team_id')->andReturn($team->id);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->actingAs($user, 'api');

        $expected = $this->makeMemberData();

        $repository = new MemberRepository(new Member());
        $actual = $repository->create($expected);

        $this->assertInstanceOf(Member::class, $actual);
        $this->assertEquals($expected['firstname'], $actual->user->firstname);
        $this->assertEquals($expected['lastname'], $actual->user->lastname);
        $this->assertEquals(['user', 'team', 'billing'], $actual->getQueueableRelations());
    }

    public function testFailsToCreateModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $repository = new MemberRepository(new Member());

        $this->expectException(\ErrorException::class);
        $repository->create([]);
    }

    public function testCreatesTeamOwnerModel(): void
    {
        $team = factory(Team::class)->create();
        $expected = $this->makeMemberData();

        $repository = new MemberRepository(new Member());
        $actual = $repository->createTeamOwner($expected, $team);

        $this->assertInstanceOf(Member::class, $actual);
        $this->assertEquals($expected['firstname'], $actual->user->firstname);
        $this->assertEquals($expected['lastname'], $actual->user->lastname);
        $this->assertEquals(['user', 'team', 'billing'], $actual->getQueueableRelations());
    }

    public function testFailsToCreateTeamOwnerModel(): void
    {
        $repository = new MemberRepository($this->member);

        $this->expectException(QueryException::class);
        $repository->createTeamOwner(['password' => 'secret'], factory(Team::class)->create());
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Member::class)->create();
        $repository = new MemberRepository(new Member());

        $expected = $this->makeMemberData();
        $actual = $repository->update($model->id, $expected);

        $this->assertInstanceOf(Member::class, $actual);
        $this->assertEquals($expected['projects'], $actual->projects()->pluck('id')->toArray());
        $this->assertEquals($expected['roles'], $actual->roles()->pluck('id')->toArray());
        $this->assertEquals($expected['billing_rate'], $actual->billing->rate);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $repository = new MemberRepository(new Member());

        $this->expectException(ModelNotFoundException::class);
        $repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Member::class)->create();
        $repository = new MemberRepository(new Member());

        $this->assertTrue($repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $member = new Member();
        $this->member->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($member);
        $repository = new MemberRepository($this->member);

        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $repository = new MemberRepository(new Member());
        $this->expectException(ModelNotFoundException::class);

        $this->assertTrue($repository->delete(0));
    }

    private function makeMemberData(): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
            'billing_rate' => $this->faker->numberBetween(1, 500),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomElement(Billing::getRateTypes()),
            'roles' => [factory(Role::class)->create()->id],
            'projects' => [factory(Project::class)->create()->id]
        ];
    }
}
