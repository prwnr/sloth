<?php

namespace Tests\Unit\Repository;

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
     * @var MockInterface
     */
    private $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currency = \Mockery::mock(Currency::class);
    }

    public function testFindsModel(): void
    {
        $expected = factory(Currency::class)->create();
        $repository = new CurrencyRepository(new Currency());

        $actual = $repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithNoRelation(): void
    {
        $expected = factory(Currency::class)->create();
        $repository = new CurrencyRepository(new Currency());

        $actual = $repository->findWith($expected->id, []);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertEmpty($actual->relationsToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository(new Currency());
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository(new Currency());
        $repository->findWith(0, []);
    }

    public function testFindsFirstModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $this->currency->shouldReceive('first')->with(['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currency);
        $actual = $repository->first();

        $this->assertInstanceOf(Currency::class, $actual);
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
        $expected = factory(Currency::class)->create();
        $repository = new CurrencyRepository(new Currency());

        $actual = $repository->findByName($expected->name);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFindModelByName(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository(new Currency());
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
        $this->assertEmpty($actual->first()->relationsToArray());
    }

    public function testCreatesModel(): void
    {
        $expected = $this->makeCurrencyData();
        $repository = new CurrencyRepository(new Currency());

        $actual = $repository->create($expected);
        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsQueryExceptionOnModelCreationWithMissingFields(): void
    {
        $this->expectException(QueryException::class);
        $repository = new CurrencyRepository(new Currency());
        $repository->create(['foo' => 'bar']);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Currency::class)->create();
        $repository = new CurrencyRepository(new Currency());
        $expected = $this->makeCurrencyData();
        $actual = $repository->update($model->id, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $repository = new CurrencyRepository(new Currency());

        $this->expectException(ModelNotFoundException::class);
        $repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Currency::class)->create();
        $repository = new CurrencyRepository(new Currency());

        $this->assertTrue($repository->delete($model->id));
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
        $repository = new CurrencyRepository(new Currency());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
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
