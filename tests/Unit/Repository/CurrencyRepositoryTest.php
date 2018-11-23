<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Mockery\MockInterface;
use Tests\TestCase;

class CurrencyRepositoryTest extends TestCase
{

    /**
     * @var CurrencyRepository
     */
    private $repository;

    /**
     * @var MockInterface
     */
    private $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CurrencyRepository(new Currency());
        $this->currency = \Mockery::mock(Currency::class);
    }

    public function testFindsModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);
        $actual = $repository->find(1);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithNoRelation(): void
    {
        $expected = new Currency($this->makeCurrencyData());

        $this->currency->shouldReceive('with->findOrFail')->once()->with([])->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);
        $actual = $repository->findWith(1, []);

        $this->assertEquals($expected, $actual);
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);
        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository($this->currency);
        $repository->find(1);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->currency->shouldReceive('with->findOrFail')
            ->once()
            ->with([])
            ->with(1, ['*'])
            ->andThrowExceptions([new ModelNotFoundException()]);
        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository($this->currency);
        $repository->findWith(1, []);
    }

    public function testFindsFirstModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $this->currency->shouldReceive('first')->with(['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);
        $actual = $repository->first();

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testReturnsNullOnFindFirstModel(): void
    {
        $this->currency->shouldReceive('first')->with(['*'])->andReturn(null);

        $repository = new CurrencyRepository($this->currency);
        $this->assertNull($repository->first());
    }

    public function testFindsModelByName(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $this->currency->shouldReceive('whereName->firstOrFail')
            ->with($expected->name)
            ->with(['*'])
            ->andReturn($expected);
        $repository = new CurrencyRepository($this->currency);

        $this->assertEquals($expected, $repository->findByName($expected->name));
    }

    public function testThrowsModelNotFoundExceptionOnFindModelByName(): void
    {
        $this->currency->shouldReceive('whereName->firstOrFail')
            ->with('foo')
            ->with(['*'])
            ->andThrow(ModelNotFoundException::class);

        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository($this->currency);
        $repository->findByName('foo');
    }

    public function testReturnCollection(): void
    {
        $expected = new Collection([
            new Currency($this->makeCurrencyData()),
            new Currency($this->makeCurrencyData()),
            new Currency($this->makeCurrencyData())
        ]);

        $this->currency->shouldReceive('all')->with(['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithNoRelation(): void
    {
        $expected = new Collection([
            new Currency($this->makeCurrencyData()),
            new Currency($this->makeCurrencyData()),
            new Currency($this->makeCurrencyData())
        ]);

        $this->currency->shouldReceive('with->get')->with([])->with(['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);
        $actual = $repository->allWith([]);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->getQueueableRelations());
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeCurrencyData();

        $actual = $this->repository->create($expected);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(QueryException::class);
        $this->repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = new Currency($this->makeCurrencyData());
        $model->exists = true;

        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($model);

        $repository = new CurrencyRepository($this->currency);
        $expected = $this->makeCurrencyData();
        $actual = $repository->update(1, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new CurrencyRepository($this->currency);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $expected->exists = true;

        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);

        $this->assertTrue($repository->delete(1));
    }

    public function testDoesNotDeleteModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());

        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);

        $this->assertFalse($repository->delete(1));
    }

    public function testThrowsModelNotFoundExceptionOnMOdelDeleteWithNotExistingModel(): void
    {
        $this->currency->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new CurrencyRepository($this->currency);

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(1);
    }

    public function makeCurrencyData(): array
    {
        return [
            'name' => $this->faker->name,
            'code' => $this->faker->currencyCode,
            'symbol' => $this->faker->randomElement(['$', 'PLN', 'C$', '€', '£'])
        ];
    }
}
