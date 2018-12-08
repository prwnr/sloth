<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserRepository
 * @package App\Repositories
 */
class UserRepository implements RepositoryInterface
{

    /**
     * @var User
     */
    private $user;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->user->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->user->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->user->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->user->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->user->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Model
    {
        $user = $this->find($id);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $user = $this->find($id);
        if ($user->delete()) {
            return true;
        }

        return false;
    }
}