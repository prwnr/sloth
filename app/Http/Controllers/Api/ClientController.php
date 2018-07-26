<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Http\Resources\Client as ClientResource;
use App\Models\Team;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ClientController
 * @package App\Http\Controllers\Api
 */
class ClientController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return ClientResource
     */
    public function index()
    {
        $clients = Client::findFromTeam(Auth::user()->team)->get();
        $clients->loadMissing('billing');
        return new ClientResource($clients);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ClientRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientRequest $request)
    {
        $data = $request->all();
        /** @var Team $team */
        $team = Auth::user()->team;

        try {
            DB::beginTransaction();
            $client = $team->clients()->create([
                'company_name' => $data['company_name'],
                'street' => $data['street'],
                'zip' => $data['zip'],
                'country' => $data['country'],
                'city' => $data['city'],
                'vat' => $data['vat'],
                'fullname' => $data['fullname'],
                'email' => $data['email']]);

            $billing = $client->billing()->create([
                'rate' => $data['billing_rate'],
                'type' => $data['billing_type'],
                'currency_id' => $data['billing_currency']
            ]);
            $client->billing()->associate($billing);
            $client->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong when creating new client. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new ClientResource($client))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client $client
     * @return ClientResource
     */
    public function show(Client $client)
    {
        $client->loadMissing('projects', 'billing', 'billing.currency');
        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ClientRequest $request
     * @param  \App\Models\Client $client
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request, Client $client)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
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
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Failed to update role. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new ClientResource($client))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        DB::beginTransaction();
        try {
            if ($client->delete() && ($client->billing && $client->billing->delete())) {
                DB::commit();
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
        }

        return response()->json([
            'message' => __('Something went wrong and project could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }
}
