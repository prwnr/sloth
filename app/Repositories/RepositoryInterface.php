<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface RepositoryInterface
 * @package App\Repositories
 */
interface RepositoryInterface
{

    /**
     * Get all models with given columns as Collection
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get all models with given columns and loaded relations as Collection
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function allWith(array $relations, array $columns = ['*']): Collection;

    /**
     * Get model by ID
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Get model by ID and return it with relations loaded
     * @param int $id
     * @param array $relations
     * @param array $columns
     * @return Model
     */
    public function findWith(int $id, array $relations, array $columns = ['*']);

    /**
     * Create new model
     * @param array $data
     * @return Model
     */
    public function create(array $data);

    /**
     * Updated existing model by ID
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data);

    /**
     * Delete model by ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}