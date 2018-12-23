<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ClientRequest;
use App\Models\Report\ClientReport;
use App\Http\Controllers\Controller;
use App\Repositories\ClientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\{JsonResource, ResourceCollection};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class ClientController
 * @package App\Http\Controllers\Api
 */
class ClientController extends Controller
{

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * ClientController constructor.
     * @param ClientRepository $clientRepository
     */
    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return new ResourceCollection($this->clientRepository->allWith(['billing']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ClientRequest $request
     * @return JsonResponse
     */
    public function store(ClientRequest $request): JsonResponse
    {
        try {
            $client = DB::transaction(function () use ($request) {
                return $this->clientRepository->create($request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when creating new client. Please try again'], Response::HTTP_BAD_REQUEST);
        }

        return (new JsonResource($client))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResource
     */
    public function show(int $id): JsonResource
    {
        $client = $this->clientRepository->findWith($id, ['projects', 'billing', 'billing.currency']);
        $report = new ClientReport(['clients' => [$client->id]]);

        $clientResource = new JsonResource($client);
        $clientResource->additional([
            'report' => $report->generate()
        ]);

        return $clientResource;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ClientRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ClientRequest $request, int $id): JsonResponse
    {
        try {
            $client = DB::transaction(function () use ($id, $request) {
                return $this->clientRepository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when updating client. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new JsonResource($client))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = DB::transaction(function() use ($id) {
                return $this->clientRepository->delete($id);
            });

            if ($result) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            report($ex);
        }

        return response()->json([
            'message' => 'Something went wrong and client could not be deleted. It may not exists, please try again'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
