<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Role;
use App\Models\Team;
use App\Repositories\ClientRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientTest extends FeatureTestCase
{

    public function testClientsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(Client::class, 5)->create(['team_id' => $this->user->team_id]);

        $response = $this->json(Request::METHOD_GET, '/api/clients');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'company_name', 'street', 'zip', 'country', 'city', 'vat', 'fullname', 'email', 'team_id', 'updated_at', 'created_at', 'id', 'billing_id',
                    'billing' => [
                        'rate', 'type', 'currency_id', 'updated_at', 'created_at', 'id'
                    ]
                ]
            ]
        ]);
        $response->assertJsonCount(5, 'data');
    }

    public function testClientsAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/clients');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testClientsAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $response = $this->json(Request::METHOD_POST, '/api/clients', $this->makeClientData());

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'company_name', 'street', 'zip', 'country', 'city', 'vat', 'fullname', 'email', 'team_id', 'updated_at', 'created_at', 'id', 'billing_id', 'billing' => [
                    'rate', 'type', 'currency_id', 'updated_at', 'created_at', 'id'
                ]
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnClientCreate(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $expectedData = $this->makeClientData();
        $repository = $this->mockAndReplaceInstance(ClientRepository::class);
        $repository->shouldReceive('create')->with($expectedData)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_POST, '/api/clients', $expectedData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong when creating new client. Please try again'
        ]);
    }

    public function testClientsAreNotCretedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();

        $this->actAsRole($role->name);

        $data = $this->makeClientData();
        $response = $this->json(Request::METHOD_POST, '/api/clients', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testClientsAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_GET, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'id' => $client->id,
                'company_name' => $client->company_name,
                'city' => $client->city,
                'zip' => $client->zip,
                'country' => $client->country,
                'street' => $client->street,
                'vat' => $client->vat,
                'fullname' => $client->fullname,
                'email' => $client->email,
                'team_id' => $client->team_id,
                'billing_id' => $client->billing_id
            ]
        ]);
    }

    public function testClientsAreNotShowedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $client = factory(Client::class)->create();
        $response = $this->json(Request::METHOD_GET, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testClientsAreNotShowedForUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $client = factory(Client::class)->create(['team_id' => $differentTeam]);
        $response = $this->json(Request::METHOD_GET, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'Not found'
        ]);
    }

    public function testClientsAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $data = $this->makeClientData();
        $response = $this->json(Request::METHOD_PUT, "/api/clients/{$client->id}", $data);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'id' => $client->id,
                'company_name' => $data['company_name'],
                'city' => $data['city'],
                'zip' => $data['zip'],
                'country' => $data['country'],
                'street' => $data['street'],
                'vat' => $data['vat'],
                'fullname' => $data['fullname'],
                'email' => $data['email']
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnClientUpdate(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $data = $this->makeClientData();

        $mock = $this->mockAndReplaceInstance(ClientRepository::class);
        $mock->shouldReceive('update')->with($client->id, $data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/clients/{$client->id}", $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong when updating client. Please try again'
        ]);
    }

    public function testClientsAreNotUpdatedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $data = $this->makeClientData();
        $response = $this->json(Request::METHOD_PUT, "/api/clients/{$client->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testClientsAreNotUpdatedByUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $client = factory(Client::class)->create(['team_id' => $differentTeam]);
        $data = $this->makeClientData();
        $response = $this->json(Request::METHOD_PUT, "/api/clients/{$client->id}", $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testClientsAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnClientDelete(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);

        $mock = $this->mockAndReplaceInstance(ClientRepository::class);
        $mock->shouldReceive('delete')->with($client->id)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_DELETE, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong and client could not be deleted. It may not exists, please try again'
        ]);
    }

    public function testErrorMessageIsReturnedWhenClientCannotBeDeleted(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);

        $mock = $this->mockAndReplaceInstance(ClientRepository::class);
        $mock->shouldReceive('delete')->with($client->id)->andReturn(false);

        $response = $this->json(Request::METHOD_DELETE, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something went wrong and client could not be deleted. It may not exists, please try again'
        ]);
    }

    public function testClientsAreNotDeletedForUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testClientsAreNotDeletedByUserFromDifferentTeam(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $differentTeam = factory(Team::class)->create()->id;
        $client = factory(Client::class)->create(['team_id' => $differentTeam]);
        $response = $this->json(Request::METHOD_DELETE, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'Not found'
        ]);
    }

    /**
     * @return array
     */
    private function makeClientData(): array
    {
        return [
            'company_name' => $this->faker->company,
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'country' => $this->faker->country,
            'street' => $this->faker->streetAddress,
            'vat' => (string)$this->faker->numberBetween(1111111, 9999999),
            'fullname' => $this->faker->name,
            'email' => $this->faker->email,
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
    }
}
