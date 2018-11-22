<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\Team\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class MemberRepository
 * @package App\Repositories
 */
class MemberRepository implements RepositoryInterface
{

    /**
     * @var Member
     */
    private $member;

    /**
     * MemberRepository constructor.
     * @param Member $member
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    /**
     * Get all models with given columns as Collection
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->member->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * Get all models with given columns and loaded relations as Collection
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->member->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * Get model by ID
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->member->findOrFail($id, $columns);
    }

    /**
     * Get model by ID and return it with relations loaded
     * @param int $id
     * @param array $relations
     * @param array $columns
     * @return Model
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->member->with($relations)->findOrFail($id, $columns);
    }

    /**
     * Create new model
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $team = Auth::user()->team;
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            $user = $team->users()->create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => $data['password']
            ]);
        }

        $member = new Member();
        $member->user()->associate($user);
        $member->team()->associate($team);
        $billing = $member->billing()->create([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
        $member->billing()->associate($billing);
        $member->save();

        $member->attachRoles($data['roles']);
        $member->projects()->sync($data['projects'] ?? []);

        return $member;
    }

    /**
     * @param array $data
     * @param Team $team
     * @return Model
     */
    public function  createTeamOwner(array $data, Team $team): Model
    {
        $data['owns_team'] = $team->id;
        $user = $team->users()->create($data);

        $member = $this->member->make();
        $member->user()->associate($user);
        $member->team()->associate($team);
        $billing = $member->billing()->create([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
        $member->billing()->associate($billing);
        $member->save();

        return $member;
    }

    /**
     * Updated existing model by ID
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data): Model
    {
        $member = $this->find($id);
        $member->roles()->sync($data['roles'] ?? []);
        $member->billing()->update([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);

        $member->projects()->sync($data['projects'] ?? []);
        $member->touch();

        return $member;
    }

    /**
     * Delete model by ID
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $member = $this->find($id);
        if ($member->delete() && ($member->billing && $member->billing->delete())) {
            return true;
        }

        return false;
    }
}