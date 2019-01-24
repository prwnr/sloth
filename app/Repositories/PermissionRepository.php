<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

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
        return $this->permission->newQuery()->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->permission->newQuery()->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     * @return Permission
     */
    public function find(int $id, array $columns = ['*']): Permission
    {
        return $this->permission->newQuery()->findOrFail($id, $columns);
    }

    /**
     * @param string $name
     * @param array $columns
     * @return Permission
     */
    public function findByName(string $name, array $columns = ['*']): Permission
    {
        return $this->permission->newQuery()->whereName($name)->firstOrFail($columns);
    }

    /**
     * {@inheritdoc}
     * @return Permission
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Permission
    {
        return $this->permission->newQuery()->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Permission
     */
    public function create(array $data): Permission
    {
        return $this->permission->newQuery()->create($data);
    }

    /**
     * {@inheritdoc}
     * @return Permission
     */
    public function update(int $id, array $data): Permission
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
        return (bool) $this->permission->newQuery()->where('id', $id)->delete();
    }
}