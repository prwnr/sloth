<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CurrencyRepository
 * @package App\Repositories
 */
class CurrencyRepository implements RepositoryInterface
{

    /**
     * @var Currency
     */
    private $currency;

    /**
     * CurrencyRepository constructor.
     * @param Currency $currency
     */
    public function __construct(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->currency->query()->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->currency->query()->with($relations)->get($columns);
    }

    /**
     * @param array $columns
     * @return Currency|null
     */
    public function first(array $columns = ['*']): ?Currency
    {
        return $this->currency->query()->first($columns);
    }

    /**
     * {@inheritdoc}
     * @return Currency
     */
    public function find(int $id, array $columns = ['*']): Currency
    {
        return $this->currency->query()->findOrFail($id, $columns);
    }

    /**
     * @param string $name
     * @param array $columns
     * @return Currency
     */
    public function findByName(string $name, array $columns = ['*']): Currency
    {
        return $this->currency->query()->whereName($name)->firstOrFail($columns);
    }

    /**
     * {@inheritdoc}
     * @return Currency
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Currency
    {
        return $this->currency->query()->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Currency
     */
    public function create(array $data): Currency
    {
        return $this->currency->query()->create($data);
    }

    /**
     * {@inheritdoc}
     * @return Currency
     */
    public function update(int $id, array $data): Currency
    {
        $currency = $this->find($id);
        $currency->update($data);

        return $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $currency = $this->find($id);

        return (bool) $currency->delete();
    }
}