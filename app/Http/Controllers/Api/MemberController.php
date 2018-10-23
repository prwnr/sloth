<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MemberRequest;
use App\Http\Resources\Project as ProjectResource;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\Models\{Report\MemberReport, Team, Team\Member, User};
use Illuminate\Http\Response;
use Illuminate\Support\{Facades\Auth, Facades\DB, Facades\Hash};
use App\Http\Controllers\Controller;
use App\Http\Resources\Member as MemberResource;

/**
 * Class MemberController
 * @package App\Http\Controllers\Api
 */
class MemberController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return MemberResource
     */
    public function index()
    {
        $members = Member::findFromTeam(Auth::user()->team)->get();
        return new MemberResource($members);
    }

    /**
     * @param Member $member
     * @return ProjectResource
     */
    public function showProjects(Member $member)
    {
        $projects = $member->projects()->where('team_id', '=', $member->user->team_id)->get();
        $projects->loadMissing('tasks');
        return new ProjectResource($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MemberRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(MemberRequest $request)
    {
        $data = $request->all();
        /** @var Team $team */
        $team = Auth::user()->team;

        try {
            DB::beginTransaction();
            [$user, $password] = $this->createUser($team, $data);

            $member = new Member();
            $member->user()->associate($user);
            $member->team()->associate($team);
            $billing = $member->billing()->create([
                'rate' => $data['billing_rate'],
                'type' => $data['billing_type'],
                'currency_id' => $data['billing_currency']
            ]);
            $member->billing()->associate($billing);
            $member->save();

            $member->attachRoles($data['roles']);
            $member->projects()->sync($data['projects'] ?? []);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' =>'Something went wrong when creating new team member. Please try again'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            Mail::to($data['email'])->send(new WelcomeMail($user, $password));
        } catch (\Exception $ex) {
            $memberResource = new MemberResource($member);
            $memberResource->additional([
                'warning' => __('There was an error during email delivery to new member with his account password.')
            ]);
            return $memberResource->response()->setStatusCode(Response::HTTP_CREATED);
        }

        return (new MemberResource($member))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team\Member $member
     * @return MemberResource
     */
    public function show(Member $member)
    {
        $member->loadMissing(['projects', 'billing', 'user', 'roles', 'billing.currency', 'logs']);

        $report = new MemberReport(['members' => [$member->user->id]]);

        $meberResource = new MemberResource($member);
        $meberResource->additional([
            'report' => $report->generate()
        ]);
        return $meberResource;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MemberRequest $request
     * @param  \App\Models\Team\Member $member
     * @return \Illuminate\Http\Response
     */
    public function update(MemberRequest $request, Member $member)
    {
        $data = $request->all();
        try {
            DB::beginTransaction();
            $member->user()->update([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname']
            ]);

            $member->roles()->sync($data['roles'] ?? []);
            $member->billing()->update([
                'rate' => $data['billing_rate'],
                'type' => $data['billing_type'],
                'currency_id' => $data['billing_currency']
            ]);

            $member->projects()->sync($data['projects'] ?? []);
            $member->touch();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => __('Failed to update team member. Please try again')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new MemberResource($member))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Team\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy(Member $member)
    {
        DB::beginTransaction();
        try {
            $user = $member->user;
            if ($user->delete() && ($member->billing && $member->billing->delete())) {
                DB::commit();
                return response()->json(null, Response::HTTP_NO_CONTENT);   
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('Something went wrong and member could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param $team
     * @param $data
     * @return array
     */
    private function createUser($team, $data): array
    {
        $password = null;
        /** @var User $user */
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            $password = str_random(10);
            $user = $team->users()->create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => Hash::make($password)
            ]);
        }

        return [$user, $password];
    }
}
