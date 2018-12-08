<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
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
        return $this->role->query()->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->role->query()->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     * @return Role
     */
    public function find(int $id, array $columns = ['*']): Role
    {
        return $this->role->query()->findOrFail($id, $columns);
    }

    /**
     * @param string $name
     * @return Role
     */
    public function findByName(string $name): ?Role
    {
        return $this->role->query()->where([
            'name' => $name,
            'team_id' => Auth::user()->team_id
        ])->first();
    }

    /**
     * {@inheritdoc}
     * @return Role
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Role
    {
        return $this->role->query()->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Role
     */
    public function create(array $data): Role
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
     * @return Role
     */
    public function update(int $id, array $data): Role
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