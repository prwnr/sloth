<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class RoleRepository
 * @package Tests\Unit\Repository
 */
class RoleRepository implements RepositoryInterface
{
    /**
     * @var Role
     */
    private $role;

    /**
     * RoleRepository constructor.
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->role->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->role->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->role->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->role->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        $user = Auth::user();
        $role = $user->team->roles()->create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description']
        ]);

        $role->members()->sync($data['members'] ?? []);
        $role->perms()->sync($data['permissions'] ?? []);

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Model
    {
        $role = $this->find($id);
        $role->update([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description']
        ]);

        $role->members()->sync($data['members'] ?? []);
        $role->perms()->sync($data['permissions'] ?? []);

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $role = $this->find($id);
        if ($role->delete()) {
            return true;
        }

        return false;
    }
}