<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\{UserPasswordRequest, UserRequest};
use App\Http\Resources\{TimeLog as TimeLogResource, User as UserResource, Users as UsersCollectionResource};
use App\Models\Team;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Repositories\MemberRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\{Facades\Auth, Facades\DB, Facades\Hash};
use Illuminate\Http\{JsonResponse, Request, Response};

/**
 * Class UserController
 * @package App\Http\Controllers\Api
 */
class UserController extends Controller
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var MemberRepository
     */
    private $memberRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     * @param MemberRepository $memberRepository
     */
    public function __construct(UserRepository $userRepository, MemberRepository $memberRepository)
    {
        $this->userRepository = $userRepository;
        $this->memberRepository = $memberRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return UsersCollectionResource
     */
    public function index(): UsersCollectionResource
    {
        return new UsersCollectionResource($this->userRepository->all());
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function timeLogs(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->userRepository->find($id);
            $logs = $this->memberRepository->timeLogs($user->member()->id, $request->all());
        } catch (\Exception $ex) {
            return response()->json(['error' => 'Date format is invalid'], Response::HTTP_BAD_REQUEST);
        }

        return (new TimeLogResource($logs))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return UserResource
     */
    public function show(int $id): UserResource
    {
        $user = $this->userRepository->findWith($id, ['members']);
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
        return new UserResource(Auth::user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UserRequest $request, int $id): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($id, $request) {
                return $this->userRepository->update($id, $request->all());
            });
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
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
     * @param int $id
     * @return JsonResponse
     */
    public function updatePassword(UserPasswordRequest $request, int $id): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($id, $request) {
                return $this->userRepository->update($id, [
                    'password' => $request->input('password')
                ]);
            });
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
