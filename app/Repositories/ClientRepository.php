<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class ClientRepository
 * @package App\Repositories
 */
class ClientRepository implements RepositoryInterface
{

    /**
     * @var Client
     */
    private $client;

    /**
     * ClientRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->client->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->client->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->client->findOrFail($id, $columns);
    }

    /**
     * @param int $id
     * @param array $relations
     * @param array $columns
     * @return Model
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->client->with($relations)->findOrFail($id, $columns);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        /** @var Team $team */
        $team = Auth::user()->team;
        $client = $team->clients()->create($data);

        $billing = $client->billing()->create([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);
        $client->billing()->associate($billing);
        $client->save();

        return $client;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data): Model
    {
        $client = $this->find($id);
        $client->update([
            'company_name' => $data['company_name'],
            'street' => $data['street'],
            'zip' => $data['zip'],
            'country' => $data['country'],
            'city' => $data['city'],
            'vat' => $data['vat'],
            'fullname' => $data['fullname'],
            'email' => $data['email']]);

        $client->billing()->update([
            'rate' => $data['billing_rate'],
            'type' => $data['billing_type'],
            'currency_id' => $data['billing_currency']
        ]);

        $client->save();

        return $client;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $client = $this->find($id);
        if ($client->delete() && ($client->billing && $client->billing->delete())) {
            return true;
        }

        return false;
    }
}