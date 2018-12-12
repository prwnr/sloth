<?php

namespace App\Repositories;

use App\Models\TodoTask;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TodoTaskRepository
 * @package App\Repositories
 */
class TodoTaskRepository implements RepositoryInterface
{

    /**
     * @var TodoTask
     */
    private $todoTask;

    /**
     * TodoTaskRepository constructor.
     * @param TodoTask $todoTask
     */
    public function __construct(TodoTask $todoTask)
    {
        $this->todoTask = $todoTask;
    }

    /**
     * Get all models with given columns as Collection
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->todoTask->query()->get($columns);
    }

    /**
     * Get all models with given columns and loaded relations as Collection
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->todoTask->query()->with($relations)->get($columns);
    }

    /**
     * Get model by ID
     * @param int $id
     * @param array $columns
     * @return TodoTask
     */
    public function find(int $id, array $columns = ['*']): TodoTask
    {
        return $this->todoTask->query()->findOrFail($id, $columns);
    }

    /**
     * Get model by ID and return it with relations loaded
     * @param int $id
     * @param array $relations
     * @param array $columns
     * @return TodoTask
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): TodoTask
    {
        return $this->todoTask->query()->with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create new model
     * @param array $data
     * @return TodoTask
     */
    public function create(array $data): TodoTask
    {
        return $this->todoTask->query()->create($data);
    }

    /**
     * Updated existing model by ID
     * @param int $id
     * @param array $data
     * @return TodoTask
     */
    public function update(int $id, array $data): TodoTask
    {
        $todoTask = $this->find($id);
        $todoTask->update($data);

        return $todoTask;
    }

    /**
     * Delete model by ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->todoTask->query()->where('id', $id)->delete();
    }
}