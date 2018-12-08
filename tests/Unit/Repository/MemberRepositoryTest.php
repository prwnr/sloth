<?php

namespace Tests\Unit\Repository;

use App\Models\Billing;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\Team\Member;
use App\Models\TimeLog;
use App\Models\User;
use App\Repositories\MemberRepository;
use Carbon\Carbon;
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

    /**
     * @var MemberRepository
     */
    private $repository;

    public function setUp(): void
    {
        $this->member = \Mockery::mock(Member::class);
        $this->repository = new MemberRepository(new Member());
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Member::class)->create();

        $actual = $this->repository->find($expected->id);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Member::class)->create();

        $expectedRelations = ['user', 'billing', 'team'];

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
        $this->repository->findWith(0, ['billing', 'user', 'team']);
    }

    public function testReturnsCollection(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Member::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Member::class, 3)->create(['team_id' => $this->user->team_id]);

        $expectedRelations = ['billing', 'user'];
        $actual = $this->repository->allWith($expectedRelations);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        foreach ($expectedRelations as $expectedRelation) {
            $this->assertTrue($actual->first()->relationLoaded($expectedRelation));
        }
    }

    public function testReturnsUserTimeLogs(): void
    {
        $member = factory(Member::class)->create();
        factory(TimeLog::class, 3)->create(['member_id' => $member->id]);

        $actual = $this->repository->timeLogs($member->id, []);

        $this->assertInstanceOf(TimeLog::class, $actual->first());
        $this->assertTrue($actual->first()->relationLoaded('project'));
        $this->assertTrue($actual->first()->relationLoaded('task'));
        $this->assertEquals(3, $actual->count());
        $this->assertEquals($member->id, $actual->first()->member_id);
    }

    public function testReturnsUserActiveTimeLogs(): void
    {
        $member = factory(Member::class)->create();
        factory(TimeLog::class, 3)->create(['member_id' => $member->id]);
        factory(TimeLog::class, 1)->create(['member_id' => $member->id, 'start' => Carbon::today()]);

        $actual = $this->repository->timeLogs($member->id, ['active' => true]);

        $this->assertInstanceOf(TimeLog::class, $actual->first());
        $this->assertTrue($actual->first()->relationLoaded('project'));
        $this->assertTrue($actual->first()->relationLoaded('task'));
        $this->assertEquals(1, $actual->count());
        $this->assertEquals($member->id, $actual->first()->member_id);
    }

    public function testReturnsUserTimeLogsFromDate(): void
    {
        $member = factory(Member::class)->create();
        $expected = factory(TimeLog::class, 3)->create(['member_id' => $member->id]);

        $actual = $this->repository->timeLogs($member->id, [
            'date' => Carbon::createFromTimeString($expected->first()->created_at)->format('Y-m-d')
        ]);

        $this->assertInstanceOf(TimeLog::class, $actual->first());
        $this->assertEquals(1, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('project'));
        $this->assertTrue($actual->first()->relationLoaded('task'));
        $this->assertEquals($member->id, $actual->first()->member_id);
    }

    public function testCreatesModel(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = $this->makeMemberData();
        $actual = $this->repository->create($expected);

        $this->assertEquals($expected['firstname'], $actual->user->firstname);
        $this->assertEquals($expected['lastname'], $actual->user->lastname);
        $this->assertEquals(['user', 'team', 'billing'], $actual->getQueueableRelations());
    }

    public function testFailsToCreateModel(): void
    {
        $this->actingAs($this->user, 'api');

        $this->expectException(\ErrorException::class);
        $this->repository->create([]);
    }

    public function testCreatesTeamOwnerModel(): void
    {
        $team = factory(Team::class)->create();
        $expected = $this->makeMemberData();

        $actual = $this->repository->createTeamOwner($expected, $team);

        $this->assertEquals($expected['firstname'], $actual->user->firstname);
        $this->assertEquals($expected['lastname'], $actual->user->lastname);
        foreach (['user', 'team', 'billing'] as $relation) {
            $this->assertTrue($actual->relationLoaded($relation));
        }
    }

    public function testFailsToCreateTeamOwnerModel(): void
    {
        $this->expectException(QueryException::class);
        $this->repository->createTeamOwner(['password' => 'secret'], factory(Team::class)->create());
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Member::class)->create();
        $expected = $this->makeMemberData();
        $actual = $this->repository->update($model->id, $expected);

        $this->assertEquals($expected['projects'], $actual->projects()->pluck('id')->toArray());
        $this->assertEquals($expected['roles'], $actual->roles()->pluck('id')->toArray());
        $this->assertEquals($expected['billing_rate'], $actual->billing->rate);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Member::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $member = new Member();
        $this->member->shouldReceive('query->findOrFail')
            ->withNoArgs()
            ->with(1, ['*'])
            ->andReturn($member);
        $repository = new MemberRepository($this->member);

        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->assertTrue($this->repository->delete(0));
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
