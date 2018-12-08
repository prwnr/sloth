<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\Team\Member;
use App\Models\User;
use Carbon\Carbon;
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
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->member->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * @param int $id
     * @param array $options ['active' => true, 'date' => true/date('Y-m-d')]
     * @return Collection
     */
    public function timeLogs(int $id, array $options): Collection
    {
        $logs = $this->find($id)->logs();
        $date = $options['date'] ?? null;
        if ($date) {
            $logs->whereDate('created_at', $this->getDateFilter($date));
        }

        if (isset($options['active'])) {
            $logs->whereNotNull('start');
        }

        $logs = $logs->get();
        $logs->loadMissing('project', 'task');

        return $logs;
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->member->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->member->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->member->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
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
                'password' => Hash::make($data['password'])
            ]);
        }

        $member = $this->member->make();
        $member->user()->associate($user);
        $member->team()->associate($team);
        $this->associateWithBilling($data, $member);
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
    public function createTeamOwner(array $data, Team $team): Model
    {
        $data['owns_team'] = $team->id;
        $data['password'] = Hash::make($data['password']);
        $user = $team->users()->create($data);

        $member = $this->member->make();
        $member->user()->associate($user);
        $member->team()->associate($team);
        $this->associateWithBilling($data, $member);
        $member->save();

        return $member;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $member = $this->find($id);
        if ($member->delete() && ($member->billing && $member->billing->delete())) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param Member $member
     */
    private function associateWithBilling(array $data, Member $member): void
    {
        $billing = $member->billing()->create([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
        $member->billing()->associate($billing);
    }
    /**
     * @param null|string $date
     * @return string
     */
    private function getDateFilter(?string $date): string
    {
        if (!$date) {
            $date = Carbon::today();
        }

        if (\is_string($date)) {
            $date = trim($date, '\"');
            [$year, $month, $day] = explode('-', $date);
            $date = Carbon::createFromDate($year, $month, $day);
        }

        return $date->format('Y-m-d');
    }

}