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
use Illuminate\Database\Eloquent\Collection;

class ClientRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $client;

    public function setUp(): void
    {
        $this->client = \Mockery::mock(Client::class);
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $expected = factory(Client::class)->create();
        $repository = new ClientRepository(new Client());

        $actual = $repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(Client::class)->create();
        $repository = new ClientRepository(new Client());
        $actual = $repository->findWith($expected->id, ['billing']);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('billing'));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $repository = new ClientRepository(new Client());

        $this->expectException(ModelNotFoundException::class);
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $repository = new ClientRepository(new Client());

        $this->expectException(ModelNotFoundException::class);
        $repository->findWith(0, ['billing']);
    }

    public function testReturnCollection(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            new Client($this->makeClientData()),
            new Client($this->makeClientData()),
            new Client($this->makeClientData())
        ]);

        $this->client->shouldReceive('where->get')
            ->with('team_id', $this->user->team_id)
            ->with(['*'])
            ->andReturn($expected);

        $repository = new ClientRepository($this->client);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            (new Client($this->makeClientData()))->setRelation('billing', new Billing($this->makeBillingData())),
            (new Client($this->makeClientData()))->setRelation('billing', new Billing($this->makeBillingData())),
            (new Client($this->makeClientData()))->setRelation('billing', new Billing($this->makeBillingData()))
        ]);

        $this->client->shouldReceive('where->with->get')
            ->with('team_id', $this->user->team_id)
            ->with(['billing'])
            ->with(['*'])
            ->andReturn($expected);

        $repository = new ClientRepository($this->client);
        $actual = $repository->allWith(['billing']);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('billing'));
    }

    public function testCreatesModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $billingData = $this->makeBillingData();
        $clientData = $this->makeClientData();
        $data = array_merge($clientData, [
            'billing_rate' => $billingData['rate'],
            'billing_currency' => $billingData['currency_id'],
            'billing_type' => $billingData['type']
        ]);

        $repository = new ClientRepository(new Client());
        $actual = $repository->create($data);

        $this->assertArraySubset($clientData, $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('billing'));
        $this->assertArraySubset($billingData, $actual->billing->attributesToArray());
    }

    public function testFailsToCreateModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $repository = new ClientRepository(new Client());

        $this->expectException(QueryException::class);
        $repository->create([]);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Client::class)->create();
        $repository = new ClientRepository(new Client());

        $billingData = $this->makeBillingData();
        $clientData = $this->makeClientData();
        $expected = array_merge($clientData, [
            'billing_rate' => $billingData['rate'],
            'billing_currency' => $billingData['currency_id'],
            'billing_type' => $billingData['type']
        ]);
        $actual = $repository->update($model->id, $expected);

        $this->assertArraySubset($clientData, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new ClientRepository($this->client);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
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
        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($model);

        $repository = new ClientRepository($this->client);

        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $repository = new ClientRepository(new Client());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
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
