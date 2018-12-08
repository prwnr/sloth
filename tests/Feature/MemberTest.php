<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\Team\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $project = factory(Project::class)->create(['team_id' => $member->team_id]);
        $member->projects()->sync([$project->id]);
        $member->save();

        $response = $this->json(Request::METHOD_GET, "/api/members/{$member->id}/projects");
        $response->assertStatus(Response::HTTP_OK);
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
        $data = [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
        $response = $this->json(Request::METHOD_POST, '/api/members', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'team_id', 'created_at', 'updated_at', 'billing_id', 'editable', 'deletable', 'user'
            ]
        ]);
    }

    public function testMembersAreNotCreatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::PROGRAMMER)->first();
        $data = [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'roles' => [$role->id],
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
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
    }

    public function testMembersAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
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
    }
}
