<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TeamRequest;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Class TeamController
 * @package App\Http\Controllers\Api
 */
class TeamController extends Controller
{

    /**
     * Update the specified resource in storage.
     *
     * @param TeamRequest $request
     * @param  \App\Models\Team $team
     * @return JsonResponse
     */
    public function update(TeamRequest $request, Team $team): JsonResponse
    {
        if (Auth::user()->owns_team !== $team->id) {
            return response()->json(['message' => 'You are not allowed to edit this team'], Response::HTTP_FORBIDDEN);
        }

        try {
            $team->update([
                'name' => $request->input('name')
            ]);

            return (new JsonResource($team))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            report($ex);
            return response()->json(['message' => 'Something went wrong when trying to update your team. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
