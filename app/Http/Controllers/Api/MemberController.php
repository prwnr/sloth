<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MemberRequest;
use App\Http\Resources\Project as ProjectResource;
use App\Mail\WelcomeMail;
use App\Repositories\MemberRepository;
use Illuminate\Http\JsonResponse;
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
     * @var MemberRepository
     */
    private $memberRepository;

    /**
     * MemberController constructor.
     * @param MemberRepository $memberRepository
     */
    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return MemberResource
     */
    public function index(): MemberResource
    {
        return new MemberResource($this->memberRepository->allWith(['roles']));
    }

    /**
     * @param Member $member
     * @return ProjectResource
     */
    public function showProjects(Member $member): ProjectResource
    {
        $projects = $member->projects()->where('team_id', '=', $member->user->team_id)->get();
        $projects->loadMissing('tasks');
        return new ProjectResource($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MemberRequest $request
     * @return JsonResponse
     */
    public function store(MemberRequest $request): JsonResponse
    {
        $data = $request->all();

        try {
            $data['password'] = str_random(10);
            $member = DB::transaction(function () use ($data) {
               return $this->memberRepository->create($data);
            });
        } catch (\Exception $ex) {
            return response()->json([
                'message' =>'Something went wrong when creating new team member. Please try again'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            Mail::to($data['email'])->send(new WelcomeMail($member->user, $data['password']));
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
     * @param int $id
     * @return MemberResource
     */
    public function show(int $id): MemberResource
    {
        $member = $this->memberRepository->findWith($id, ['projects', 'billing', 'user', 'roles', 'billing.currency', 'logs']);

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
     * @param int $id
     * @return JsonResponse
     */
    public function update(MemberRequest $request, int $id): JsonResponse
    {
        try {
            $member = DB::transaction(function () use ($id, $request) {
                return $this->memberRepository->update($id, $request->all()) ;
            });

        } catch (\Exception $ex) {
            return response()->json([
                'message' => __('Failed to update team member. Please try again')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new MemberResource($member))->response()->setStatusCode(Response::HTTP_ACCEPTED);
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
            $success = DB::transaction(function () use ($id) {
                return $this->memberRepository->delete($id);
            });

            if ($success) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('Something went wrong and member could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }
}
