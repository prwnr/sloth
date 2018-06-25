<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Project as ProjectResource;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\Models\{
    Project, Team, Team\Member, User
};
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\{
    Facades\Auth, Facades\DB, Facades\Hash
};
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
     * @return \Illuminate\Http\Response
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request->all())->validate();
        $data = $request->all();
        /** @var Team $team */
        $team = Auth::user()->team;

        try {
            DB::beginTransaction();
            $password = str_random(10);
            /** @var User $user */
            $user = $team->users()->create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => Hash::make($password)
            ]);

            $user->attachRoles($data['roles']);
            $user->save();
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
        $member->loadMissing(['projects', 'billing', 'user.roles', 'billing.currency']);        
        return new MemberResource($member);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Member $member)
    {
        $this->validator($request->all(), $member->user)->validate();
        $data = $request->all();

        try {
            DB::beginTransaction();
            $member->user()->update([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
            ]);

            $member->user->roles()->sync($data['roles'] ?? []);
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
            'message' => __('Something went wrong and role could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array $data
     * @param User|null $user
     * @return Validator
     */
    private function validator(array $data, $user = null): Validator
    {
        return \Illuminate\Support\Facades\Validator::make($data, [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email' . ($user ? ',' . $user->id : ''), //TODO make user email unique per team
            'roles' => 'required|array|min:1',
            'billing_rate' => 'required|numeric|between:0,999.99',
            'billing_currency' => 'required|numeric',
            'billing_type' => 'required|string',
            'projects' => 'nullable|array'
        ]);
    }
}
