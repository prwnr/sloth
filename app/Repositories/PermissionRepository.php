<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PermissionRepository
 * @package App\Repositories
 */
class PermissionRepository implements RepositoryInterface
{

    /**
     * @var Permission
     */
    private $permission;

    /**
     * PermissionRepository constructor.
     * @param Permission $permission
     */
    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->permission->all($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->permission->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->permission->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->permission->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        return $this->permission->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Model
    {
        $permission = $this->find($id);
        $permission->update($data);

        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $permission = $this->find($id);

        if ($permission->delete()) {
            return true;
        }

        return false;
    }
}