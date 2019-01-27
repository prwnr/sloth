<?php

namespace Tests\Unit\Repository;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Team;
use App\Models\User;
use App\Repositories\ClientRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Mockery\MockInterface;
use Tests\TestCase;

class ClientRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $client;

    /**
     * @var ClientRepository
     */
    private $repository;

    public function setUp(): void
    {
        $this->client = \Mockery::mock(Client::class);
        $this->repository = new ClientRepository(new Client());
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $expected = factory(Client::class)->create();

        $actual = $this->repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(Client::class)->create();
        $actual = $this->repository->findWith($expected->id, ['billing']);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('billing'));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findWith(0, ['billing']);
    }

    public function testReturnsCollection(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Client::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');
        $expected = factory(Client::class, 3)->create(['team_id' => $this->user->team_id]);

        $actual = $this->repository->allWith(['billing']);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('billing'));
    }

    public function testCreatesModel(): void
    {
        $this->actingAs($this->user, 'api');

        $billingData = $this->makeBillingData();
        $clientData = $this->makeClientData();
        $data = array_merge($clientData, [
            'billing_rate' => $billingData['rate'],
            'billing_currency' => $billingData['currency_id'],
            'billing_type' => $billingData['type']
        ]);

        $actual = $this->repository->create($data);

        $this->assertArraySubset($clientData, $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('billing'));
        $this->assertArraySubset($billingData, $actual->billing->attributesToArray());
    }

    public function testFailsToCreateModel(): void
    {
        $this->actingAs($this->user, 'api');

        $this->expectException(QueryException::class);
        $this->repository->create([]);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Client::class)->create();

        $billingData = $this->makeBillingData();
        $clientData = $this->makeClientData();
        $expected = array_merge($clientData, [
            'billing_rate' => $billingData['rate'],
            'billing_currency' => $billingData['currency_id'],
            'billing_type' => $billingData['type']
        ]);
        $actual = $this->repository->update($model->id, $expected);

        $this->assertArraySubset($clientData, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->client->shouldReceive('query->findOrFail')
            ->withNoArgs()
            ->with(1, ['*'])
            ->andThrows(new ModelNotFoundException());

        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Client::class)->create();
        $repository = new ClientRepository(new Client());

        $this->assertTrue($repository->delete($model->id));
    }

    public function testDoesNotDeleteModel(): void
    {
        $model = new Client($this->makeClientData());
        $this->client->shouldReceive('newQuery->findOrFail')
            ->withNoArgs()
            ->with(1, ['*'])
            ->andReturn($model);

        $repository = new ClientRepository($this->client);
        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->delete(0);
    }

    private function makeClientData(): array
    {
        return [
            'company_name' => $this->faker->company,
            'street' => $this->faker->streetAddress,
            'zip' => $this->faker->postcode,
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'vat' => $this->faker->numberBetween(11111,99999),
            'fullname' => $this->faker->name,
            'email' => $this->faker->email
        ];
    }

    private function makeBillingData(): array
    {
        return [
            'rate' => $this->faker->numberBetween(1, 500),
            'currency_id' => Currency::all()->random()->id,
            'type' => $this->faker->randomElement(Billing::getRateTypes())
        ];
    }
}
