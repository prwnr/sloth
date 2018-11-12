<?php

namespace Tests\Unit;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Team;
use App\Models\User;
use App\Repositories\ClientRepository;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

class ClientRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $client;

    /**
     * @var MockInterface
     */
    private $collection;

    public function setUp(): void
    {
        $this->client = \Mockery::mock(Client::class);
        $this->collection = \Mockery::mock(Collection::class);
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $this->client->shouldReceive('findOrFail')->once()->with(1, ['*'])->andReturn($this->client);

        $repository = new ClientRepository($this->client);

        $this->assertEquals($this->client, $repository->find(1));
    }

    public function testFindsModelWithRelation(): void
    {
        $this->client->shouldReceive('with->findOrFail')->once()->with(['billing'])->with(1, ['*'])->andReturn($this->client);

        $repository = new ClientRepository($this->client);

        $this->assertEquals($this->client, $repository->findWith(1, ['billing']));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->client->shouldReceive('findOrFail')->once()->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new ClientRepository($this->client);

        $this->expectException(ModelNotFoundException::class);
        $repository->find(1);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->client->shouldReceive('with->findOrFail')
            ->once()
            ->with(['billing'])
            ->with(1, ['*'])
            ->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new ClientRepository($this->client);

        $this->expectException(ModelNotFoundException::class);
        $repository->findWith(1, ['billing']);
    }

    public function testReturnCollection(): void
    {
        $this->mockTeamAndUser();
        $this->actingAs($this->user, 'api');
        $this->client->shouldReceive('where->get')
            ->once()
            ->with('team_id', $this->user->team_id)
            ->with(['*'])
            ->andReturn($this->collection);

        $repository = new ClientRepository($this->client);

        $this->assertEquals($this->collection, $repository->all());
    }

    public function testReturnCollectionWithRelations(): void
    {
        $this->mockTeamAndUser();
        $this->actingAs($this->user, 'api');
        $this->client->shouldReceive('where->with->get')
            ->once()
            ->with('team_id', $this->user->team_id)->with('billing')->with(['*'])->andReturn($this->collection);

        $repository = new ClientRepository($this->client);

        $this->assertEquals($this->collection, $repository->allWith(['billing']));
    }

    public function testCreatesModel(): void
    {
        $this->mockTeamAndUser();
        $this->actingAs($this->user, 'api');

        $clientData = $this->makeClientData();
        $billingData = $this->makeBillingData();
        $data = array_merge($clientData, $billingData);

        $billing = \Mockery::mock(Billing::class);
        $belongsTo = \Mockery::mock(BelongsTo::class);
        $this->client->shouldReceive('billing')->withNoArgs()->andReturn($belongsTo);

        $this->team->shouldReceive('clients->create')->with($data)->andReturn($this->client);
        $belongsTo->shouldReceive('create')->andReturn($billing);
        $belongsTo->shouldReceive('associate')->with($billing)->andReturn($this->client);
        $this->client->shouldReceive('save')->withNoArgs()->andReturn(true);

        $repository = new ClientRepository($this->client);

        $actual = $repository->create($data);
        $this->assertEquals($this->client, $actual);
    }

    public function testFailsToCreateModel(): void
    {
        $this->mockTeamAndUser();
        $this->actingAs($this->user, 'api');

        $this->team->shouldReceive('clients->create')->with([])->andThrowExceptions([new QueryException()]);

        $repository = new ClientRepository($this->client);

        $this->expectException(QueryException::class);
        $repository->create([]);
    }

    public function testUpdatesModel(): void
    {
        $clientData = $this->makeClientData();
        $billingData = $this->makeBillingData();
        $data = array_merge($clientData, $billingData);

        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($this->client);
        $this->client->shouldReceive('update')->with($clientData)->andReturn(true);
        $this->client->shouldReceive('billing->update')->andReturn(true);
        $this->client->shouldReceive('save')->andReturn(true);

        $repository = new ClientRepository($this->client);

        $this->assertEquals($this->client, $repository->update(1, $data));
    }

    public function testFailsToUpdateModel(): void
    {
        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new ClientRepository($this->client);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($this->client);
        $this->client->shouldReceive('delete')->andReturn(true);

        $billing = \Mockery::mock(Billing::class);
        $this->client->shouldReceive('getAttribute')->with('billing')->andReturn($billing);
        $billing->shouldReceive('delete')->andReturn(true);

        $repository = new ClientRepository($this->client);

        $this->assertTrue($repository->delete(1));
    }

    public function testDontDeletesModel(): void
    {
        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($this->client);
        $this->client->shouldReceive('delete')->andReturn(false);

        $billing = \Mockery::mock(Billing::class);
        $this->client->shouldReceive('getAttribute')->with('billing')->andReturn($billing);
        $billing->shouldReceive('delete')->andReturn(false);

        $repository = new ClientRepository($this->client);

        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $this->client->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);
        $repository = new ClientRepository($this->client);

        $this->expectException(ModelNotFoundException::class);
        $this->assertTrue($repository->delete(1));
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
            'billing_rate' => $this->faker->numberBetween(1, 500),
            'billing_currency' => Currency::all()->random()->id,
            'billing_type' => $this->faker->randomElement(Billing::getRateTypes())
        ];
    }
}
