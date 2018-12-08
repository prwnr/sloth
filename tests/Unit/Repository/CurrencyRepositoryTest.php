<?php

namespace Tests\Unit\Repository;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class CurrencyRepositoryTest extends TestCase
{

    /**
     * @var CurrencyRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CurrencyRepository(new Currency());

        Currency::query()->delete(); // Currencies are pre-installed, for this repository tests we dont want them
    }

    public function testFindsModel(): void
    {
        $expected = factory(Currency::class)->create();

        $actual = $this->repository->find($expected->id);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithNoRelation(): void
    {
        $expected = factory(Currency::class)->create();

        $actual = $this->repository->findWith($expected->id, []);

        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertEmpty($actual->relationsToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findWith(0, []);
    }

    public function testFindsFirstModel(): void
    {
        $expected = factory(Currency::class)->create();
        $actual = $this->repository->first();

        $this->assertInstanceOf(Currency::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testReturnsNullOnFindFirstModel(): void
    {
        $this->assertNull($this->repository->first());
    }

    public function testFindsModelByName(): void
    {
        $expected = factory(Currency::class)->create();

        $actual = $this->repository->findByName($expected->name);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnFindModelByName(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findByName('foo');
    }

    public function testReturnsCollection(): void
    {
        $expected = factory(Currency::class, 3)->create();
        $actual = $this->repository->all();

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnsCollectionWithNoRelation(): void
    {
        $expected = factory(Currency::class, 3)->create();
        $actual = $this->repository->allWith([]);

        $this->assertEquals($expected->first()->attributesToArray(), $actual->first()->attributesToArray());
        $this->assertEquals(3, $actual->count());
        $this->assertEmpty($actual->first()->relationsToArray());
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
        $model = factory(Currency::class)->create();
        $expected = $this->makeCurrencyData();
        $actual = $this->repository->update($model->id, $expected);

        $this->assertArraySubset($expected, $actual->attributesToArray());
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->update(0, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Currency::class)->create();
        $this->assertTrue($this->repository->delete($model->id));
    }

    public function testDoesNotDeleteNotExistingModel(): void
    {
        $this->assertFalse($this->repository->delete(0));
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
