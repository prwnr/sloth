<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientTest extends FeatureTestCase
{

    public function testClientsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        for ($i = 0; $i < 5; $i++) {
            factory(Client::class)->create([
                'team_id' => $this->user->team_id
            ]);
        }

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

    public function testClientsAreCreatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $data = [
            'company_name' => $this->faker->company,
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'country' => $this->faker->country,
            'street' => $this->faker->streetAddress,
            'vat' => (string) $this->faker->numberBetween(1111111, 9999999),
            'fullname' => $this->faker->name,
            'email' => $this->faker->email,
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
        $response = $this->json(Request::METHOD_POST, '/api/clients', $data);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'company_name', 'street', 'zip', 'country', 'city', 'vat', 'fullname', 'email', 'team_id', 'updated_at', 'created_at', 'id', 'billing_id', 'billing' => [
                    'rate', 'type', 'currency_id', 'updated_at', 'created_at', 'id'
                ]
            ]
        ]);
    }

    public function testClientsAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $client = factory(Client::class)->create();
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

    public function testClientsAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $client = factory(Client::class)->create();
        $data = [
            'company_name' => $this->faker->company,
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'country' => $this->faker->country,
            'street' => $this->faker->streetAddress,
            'vat' => (string) $this->faker->numberBetween(1111111, 9999999),
            'fullname' => $this->faker->name,
            'email' => $this->faker->email,
            'billing_rate' => $this->faker->numberBetween(0, 50),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomKey(Billing::getRateTypes())
        ];
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

    public function testClientsAreDeletedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $client = factory(Client::class)->create(['team_id' => $this->user->team_id]);
        $response = $this->json(Request::METHOD_DELETE, "/api/clients/{$client->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
