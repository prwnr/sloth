<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TimeLog as TimeLogResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Users as UsersCollectionResource;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Return logged in user
     * 
     * @return \Illuminate\Http\Response
     */
    public function showActive()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'firstname' => 'required|string|max:255',
            'skin' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, //TODO make user email unique per team
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        $data = $request->all();
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->email = $data['email'];
        $user->skin = $data['skin'];

        try {
            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }    
    }

    /**
     * @param Request $request
     * @param User $user
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->validate($request, [
            'password' => 'required|string|min:6|confirmed'
        ]);

        try {
            $user->update([
                'password' => Hash::make($request->input('password'))
            ]);
            return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
