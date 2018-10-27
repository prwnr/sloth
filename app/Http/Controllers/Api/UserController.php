<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\{UserPasswordRequest, UserRequest};
use App\Http\Resources\{TimeLog as TimeLogResource, User as UserResource, Users as UsersCollectionResource};
use App\Models\Team;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\{Facades\Auth, Facades\Hash};
use Illuminate\Http\{JsonResponse, Request, Response};

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
    public function index(): UsersCollectionResource
    {
        $users = User::findFromTeam(Auth::user()->team)->get();
        return new UsersCollectionResource($users);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return TimeLogResource
     */
    public function timeLogs(Request $request, User $user): TimeLogResource
    {
        $logs = $user->member()->logs();

        try {
            $date = $request->get('date');
            if ($date) {
                $dateFilter = $this->getDateFilter($date);
                $logs->whereDate('created_at', $dateFilter);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => 'Date format is invalid'], Response::HTTP_BAD_REQUEST);
        }

        $active = $request->get('active');
        if ($active) {
            $logs->whereNotNull('start');
        }

        $logs = $logs->get();
        $logs->loadMissing('project', 'task');
        return new TimeLogResource($logs);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        $teams = [];
        foreach ($user->members as $member) {
            $teams[] = [
                'id' => $member->team->id,
                'name' => $member->team->name
            ];
        }

        $resource = new UserResource($user);
        $resource->additional([
            'teams' => $teams
        ]);
        return $resource;
    }

    /**
     * Return logged in user
     *
     * @return UserResource
     */
    public function showActive(): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UserRequest $request, User $user): JsonResponse
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
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function switchTeam(Request $request, User $user): JsonResponse
    {
        try {
            $team = Team::find($request->input('team'));
            $user->team()->associate($team);
            $user->save();

            return response()->json($user->getAllInfoData())->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UserPasswordRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function updatePassword(UserPasswordRequest $request, User $user): JsonResponse
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
