<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
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
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->client->query()->where('team_id', Auth::user()->team_id)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->client->query()->where('team_id', Auth::user()->team_id)->with($relations)->get($columns);
    }

    /**
     * {@inheritdoc}
     * @return Client
     */
    public function find(int $id, array $columns = ['*']): Client
    {
        return $this->client->query()->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Client
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Client
    {
        return $this->client->query()->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return Client
     */
    public function create(array $data): Client
    {
        /** @var Team $team */
        $team = Auth::user()->team;
        /** @var Client $client */
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
     * {@inheritdoc}
     * @return Client
     */
    public function update(int $id, array $data): Client
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
     * {@inheritdoc}
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