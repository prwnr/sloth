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
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->todoTask->query()->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->todoTask->query()->with($relations)->get($columns);
    }

    /**
     * @param int $memberId
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function allOfMemberWith(int $memberId, array $relations, array $columns = ['*']): Collection
    {
        return $this->todoTask->query()->where('member_id', $memberId)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     * @return TodoTask
     */
    public function find(int $id, array $columns = ['*']): TodoTask
    {
        return $this->todoTask->query()->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return TodoTask
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): TodoTask
    {
        return $this->todoTask->query()->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return TodoTask
     */
    public function create(array $data): TodoTask
    {
        return $this->todoTask->query()->create($data);
    }

    /**
     * {@inheritdoc}
     * @return TodoTask
     */
    public function update(int $id, array $data): TodoTask
    {
        $todoTask = $this->find($id);
        $todoTask->update($data);

        return $todoTask;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        return $this->todoTask->query()->where('id', $id)->delete();
    }
}