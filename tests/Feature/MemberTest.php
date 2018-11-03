<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Currency;
use App\Models\Role;
use App\Models\Team\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemberTest extends FeatureTestCase
{

    public function testMembersAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        for ($i = 0; $i < 5; $i++) {
            factory(Member::class)->create([
                'team_id' => $this->user->team_id
            ]);
        }

        $response = $this->json(Request::METHOD_GET, '/api/members');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['data' => [
            [
                'id', 'user_id', 'team_id', 'created_at', 'updated_at', 'billing_id', 'editable', 'deletable', 'user'
            ]
        ]]);
        $response->assertJsonCount(6, 'data');
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

    public function testMembersAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $member = factory(Member::class)->create();
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

    public function testMembersAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $member = factory(Member::class)->create();
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

    public function testMembersAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $member = factory(Member::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/members/{$member->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
