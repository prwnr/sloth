<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleTest extends FeatureTestCase
{
    public function testRolesAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->json(Request::METHOD_GET, '/api/roles');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'name', 'display_name', 'description', 'created_at', 'updated_at', 'team_id', 'editable', 'deletable'
                ]
            ]
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function testRolesAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/roles');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testRolesAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $data = [
            'name' => $this->faker->toLower($this->faker->word),
            'display_name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'members' => [],
            'permissions' => []
        ];
        $response = $this->json(Request::METHOD_POST, '/api/roles', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'display_name', 'description', 'created_at', 'updated_at', 'team_id', 'editable', 'deletable'
            ]
        ]);
    }

    public function testRolesAreNotCreatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $data = [
            'name' => $this->faker->toLower($this->faker->word),
            'display_name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'members' => [],
            'permissions' => []
        ];
        $response = $this->json(Request::METHOD_POST, '/api/roles', $data);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testRolesWithAlreadyExistingNameAreNotCreated(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $data = [
            'name' => $this->faker->toLower(Role::ADMIN),
            'display_name' => Role::ADMIN,
            'description' => $this->faker->sentence,
            'members' => [],
            'permissions' => []
        ];
        $response = $this->json(Request::METHOD_POST, '/api/roles', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => 'Role with this name already exists'
        ]);
    }

    public function testRolesAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        /** @var Role $role */
        $role = Role::findFromTeam($this->user->team)->where('name', Role::ADMIN)->first();
        $response = $this->json(Request::METHOD_GET, "/api/roles/{$role->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'editable' => $role->editable,
                'deletable' => $role->deletable
            ]
        ]);
    }

    public function testRolesAreNotShowedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::ADMIN)->first();
        $response = $this->json(Request::METHOD_GET, "/api/roles/{$role->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testRolesAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $role = factory(Role::class)->create();
        $data = [
            'name' => $this->faker->toLower($this->faker->word),
            'display_name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/roles/{$role->id}", $data);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'id' => $role->id,
                'name' => $data['display_name'],
                'display_name' => $data['display_name'],
                'description' => $data['description']
            ]
        ]);
    }

    public function testRolesAreNotUpdatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $role = factory(Role::class)->create();
        $data = [
            'name' => $this->faker->toLower($this->faker->word),
            'display_name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/roles/{$role->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testRolesAreNotUpdatedWhenNotEditable(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $role = Role::findFromTeam($this->user->team)->where('name', Role::ADMIN)->first();
        $data = [
            'name' => $this->faker->toLower($this->faker->word),
            'display_name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/roles/{$role->id}", $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => 'You cannot edit this role'
        ]);
    }

    public function testRolesAreNotUpdatedWhenNameAlreadyExists(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $role = factory(Role::class)->create(['team_id' => $this->user->team_id]);
        $data = [
            'name' => $this->faker->toLower(Role::PROGRAMMER),
            'display_name' => Role::PROGRAMMER,
            'description' => $this->faker->sentence,
        ];

        $response = $this->json(Request::METHOD_PUT, "/api/roles/{$role->id}", $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => 'Role with this name already exists'
        ]);
    }

    public function testRolesAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $role = factory(Role::class)->create();
        $response = $this->json(Request::METHOD_DELETE, "/api/roles/{$role->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testRolesAreNotDeletedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $role = factory(Role::class)->create();
        $response = $this->json(Request::METHOD_DELETE, "/api/roles/{$role->id}");
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testRolesAreNotDeletedWhenNotDeletable(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $role = Role::findFromTeam($this->user->team)->where('name', Role::ADMIN)->first();
        $response = $this->json(Request::METHOD_DELETE, "/api/roles/{$role->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'You can\'t delete this role'
        ]);
    }
}
