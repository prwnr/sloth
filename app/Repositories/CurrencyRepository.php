<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
        $this->currency->query()->first();
        return $this->currency->all($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->currency->with($relations)->get($columns);
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function first(array $columns = ['*'])
    {
        return $this->currency->first($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->currency->findOrFail($id, $columns);
    }

    /**
     * @param string $name
     * @param array $columns
     * @return Model
     */
    public function findByName(string $name, array $columns = ['*']): Model
    {
        return $this->currency->whereName($name)->firstOrFail($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->currency->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        return $this->currency->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Model
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