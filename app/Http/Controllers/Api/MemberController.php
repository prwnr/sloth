<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MemberRequest;
use App\Mail\WelcomeMail;
use App\Repositories\MemberRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\{JsonResource, ResourceCollection};
use Illuminate\Support\Facades\Mail;
use App\Models\{Report\MemberReport};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return new ResourceCollection($this->memberRepository->allWith(['roles']));
    }

    /**
     * @param int $id
     * @return JsonResource
     */
    public function showProjects(int $id): JsonResource
    {
        $member = $this->memberRepository->find($id);
        $projects = $member->projects()->where('team_id', '=', $member->user->team_id)->get();
        $projects->loadMissing('tasks');

        return new JsonResource($projects);
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
            report($ex);
            return response()->json([
                'message' => 'Something went wrong when creating new team member. Please try again'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $memberResource = new JsonResource($member);

        try {
            Mail::to($data['email'])->send(new WelcomeMail($member->user, $data['password']));
        } catch (\Exception $ex) {
            report($ex);
            $memberResource->additional([
                'warning' => 'There was an error during email delivery to new member with his account password.'
            ]);
        } finally {
            return $memberResource->response()->setStatusCode(Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResource
     */
    public function show(int $id): JsonResource
    {
        $member = $this->memberRepository->findWith($id, ['projects', 'billing', 'user', 'roles', 'billing.currency', 'logs']);

        $report = new MemberReport(['members' => [$member->user->id]]);

        $meberResource = new JsonResource($member);
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
            report($ex);
            return response()->json([
                'message' => 'Something went wrong when updating team member. Please try again'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new JsonResource($member))->response()->setStatusCode(Response::HTTP_ACCEPTED);
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
            report($ex);
        }

        return response()->json([
            'message' => 'Something went wrong and member could not be deleted. It may not exists, please try again'
        ], Response::HTTP_BAD_REQUEST);
    }
}
