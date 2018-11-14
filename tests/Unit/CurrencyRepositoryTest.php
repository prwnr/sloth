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
    private $currencyMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CurrencyRepository(new Currency());
        $this->currencyMock = \Mockery::mock(Currency::class);
    }

    public function testFindsModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currencyMock);
        $actual = $repository->find(1);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testModelHasNoRelation(): void
    {
        $expected = new Currency($this->makeCurrencyData());
        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currencyMock);

        $this->assertEmpty($repository->find(1)->getRelations());
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);
        $this->expectException(ModelNotFoundException::class);

        $repository = new CurrencyRepository($this->currencyMock);
        $repository->find(1);
    }

    public function testReturnCollection(): void
    {
        $expected = new Collection([
            new Currency($this->makeCurrencyData()),
            new Currency($this->makeCurrencyData()),
            new Currency($this->makeCurrencyData())
        ]);

        $this->currencyMock->shouldReceive('all')->with(['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currencyMock);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
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
        $data = $this->makeCurrencyData();
        $expected = new Currency($data);

        $this->currencyMock->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currencyMock);
        $actual = $repository->update($this->currencyMock->id, $data);

        $this->assertArraySubset($data, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new CurrencyRepository($this->currencyMock);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $expected = factory(Currency::class)->create();

        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currencyMock);

        $this->assertTrue($repository->delete(1));
    }

    public function testDoesNotDeleteModel(): void
    {
        $expected = new Currency($this->makeCurrencyData());

        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($expected);

        $repository = new CurrencyRepository($this->currencyMock);

        $this->assertFalse($repository->delete(1));
    }

    public function testThrowsModelNotFoundExceptionOnMOdelDeleteWithNotExistingModel(): void
    {
        $this->currencyMock->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new CurrencyRepository($this->currencyMock);

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
