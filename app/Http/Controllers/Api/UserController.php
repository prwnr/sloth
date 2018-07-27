<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\{UserPasswordRequest, UserRequest};
use App\Http\Resources\{
    TimeLog as TimeLogResource,
    User as UserResource,
    Users as UsersCollectionResource
};
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\{Facades\Auth, Facades\Hash};
use Illuminate\Http\{Request, Response};

/**
 * Class UserController
 * @package App\Http\Controllers\Api
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return UsersCollectionResource
     */
    public function index()
    {
        $users = User::findFromTeam(Auth::user()->team)->get();
        return new UsersCollectionResource($users);
    }

    /**
     * @param User $user
     * @return TimeLogResource
     */
    public function times(User $user)
    {
        $times = $user->times;
        $times->loadMissing('project', 'task');
        return new TimeLogResource($times);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Return logged in user
     *
     * @return UserResource
     */
    public function showActive()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserRequest $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $data = $request->all();

        try {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $user->update($data);

            return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }    
    }

    /**
     * @param UserPasswordRequest $request
     * @param User $user
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function updatePassword(UserPasswordRequest $request, User $user)
    {
        try {
            $user->update([
                'password' => Hash::make($request->input('password'))
            ]);
            return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
