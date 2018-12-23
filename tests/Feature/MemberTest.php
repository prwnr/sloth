<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\Team\Member;
use App\Repositories\MemberRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class MemberTest extends FeatureTestCase
{

    public function testMembersAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(Member::class, 5)->create(['team_id' => $this->user->team_id]);

        $response = $this->json(Request::METHOD_GET, '/api/members');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['data' => [
            [
                'id', 'user_id', 'team_id', 'created_at', 'updated_at', 'billing_id', 'editable', 'deletable', 'user', 'roles'
            ]
        ]]);
        $response->assertJsonCount(6, 'data');
    }

    public function testMembersAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/members');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testMemberProjectsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        /** @var Member $member */
        $member = factory(Member::class)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->user->team_id
        ]);
        $projects = factory(Project::class, 3)->create(['team_id' => $member->team_id])->pluck('id')->toArray();
        $member->projects()->sync($projects);
        $member->save();

        $response = $this->json(Request::METHOD_GET, "/api/members/{$member->id}/projects");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'code', 'name', 'budget', 'budget_period', 'budget_currency_id', 'client_id', 'team_id', 'billing_id', 'created_at', 'updated_at', 'tasks'
                ]
            ]
        ]);
    }

    public function testMemberProjectsAreNotListedForGuest(): void
    {
        /** @var Member $member */
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $project = factory(Project::class)->create(['team_id' => $member->team_id]);
        $member->projects()->sync([$project->id]);
        $member->save();

        $response = $this->json(Request::METHOD_GET, "/api/members/{$member->id}/projects");
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testMembersAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = $this->makeMemberData($role);
        $response = $this->json(Request::METHOD_POST, '/api/members', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'team_id', 'created_at', 'updated_at', 'billing_id', 'editable', 'deletable', 'user'
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnMemberCreate(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = $this->makeMemberData($role);

        $mock = $this->mockAndReplaceInstance(MemberRepository::class);
        $mock->shouldReceive('create')->with($data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_POST, '/api/members', $data);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertExactJson([
            'message' => 'Something went wrong when creating new team member. Please try again'
        ]);
    }

    public function testWarningMessageIsReturnedWhenMemberIsCreatedAndEmailNotSent(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = $this->makeMemberData($role);

        Mail::shouldReceive('to->send')->with($data['email'])->withAnyArgs()->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_POST, '/api/members', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'team_id', 'created_at', 'updated_at', 'billing_id', 'editable', 'deletable', 'user'
            ],
            'warning'
        ]);
        $response->assertJson([
            'warning' => 'There was an error during email delivery to new member with his account password.'
        ]);
    }

    public function testMembersAreNotCreatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = $this->makeMemberData($role);
        $response = $this->json(Request::METHOD_POST, '/api/members', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testMembersAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_GET, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'id' => $member->id,
                'editable' => $member->editable,
                'deletable' => $member->deletable,
                'billing_id' => $member->billing_id,
                'logs' => [],
                'projects' => [],
                'roles' => [],
                'team_id' => $member->team_id,
                'user' => [],
                'user_id' => $member->user_id,
            ]
        ]);
    }

    public function testMembersAreNotShowedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_GET, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testMembersAreNotShowedForUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $member = factory(Member::class)->create(['team_id' => $differentTeam]);

        $response = $this->json(Request::METHOD_GET, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'Not found'
        ]);
    }

    public function testMembersAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = [
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
        $response = $this->json(Request::METHOD_PUT, "/api/members/{$member->id}", $data);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'id' => $member->id
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnMemberUpdate(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = [
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];

        $mock = $this->mockAndReplaceInstance(MemberRepository::class);
        $mock->shouldReceive('update')->with($member->id, $data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/members/{$member->id}", $data);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson([
            'message' => 'Something went wrong when updating team member. Please try again'
        ]);
    }

    public function testMembersAreNotUpdatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $member = factory(Member::class)->create();
        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = [
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
        $response = $this->json(Request::METHOD_PUT, "/api/members/{$member->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testMembersAreNotUpdatedByUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $member = factory(Member::class)->create(['team_id' => $differentTeam]);
        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = [
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
        $response = $this->json(Request::METHOD_PUT, "/api/members/{$member->id}", $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'Not found'
        ]);
    }

    public function testMembersAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnMemberDelete(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);

        $mock = $this->mockAndReplaceInstance(MemberRepository::class);
        $mock->shouldReceive('delete')->with($member->id)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => 'Something went wrong and member could not be deleted. It may not exists, please try again'
        ]);
    }

    public function testErrorMessageIsReturnedWhenMemberCannotBeDeleted(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);

        $mock = $this->mockAndReplaceInstance(MemberRepository::class);
        $mock->shouldReceive('delete')->with($member->id)->andReturn(false);

        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => 'Something went wrong and member could not be deleted. It may not exists, please try again'
        ]);
    }

    public function testMembersAreNotDeletedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testMembersAreNotDeletedByUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $member = factory(Member::class)->create(['team_id' => $differentTeam]);
        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");;

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'Not found'
        ]);
    }

    /**
     * @param $role
     * @return array
     */
    private function makeMemberData($role): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
    }
}
