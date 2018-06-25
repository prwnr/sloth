<?php

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Http\Resources\Team as TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

/**
 * Class TeamController
 * @package App\Http\Controllers\Api
 */
class TeamController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Team $team)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255'
        ]);
        
        try {
            $team->update([
                'name' => $request->input('name')
            ]);

            return (new TeamResource($team))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
