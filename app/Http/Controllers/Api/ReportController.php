<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\CustomRange;
use App\Models\Report\{Periodic\HoursReport, FullReport, Periodic\UserProjectsReport};
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};
use App\Http\Controllers\Controller;

/**
 * Class ReportController
 * @package App\Http\Controllers\Api
 */
class ReportController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->createFullReport($request->input('filters')));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $options = $request->input('filters');
        $options['members'] = [$user->id];

        return response()->json($this->createFullReport($options));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param string $period
     * @return JsonResponse
     */
    public function userHours(Request $request, User $user, string $period): JsonResponse
    {
        $report = new HoursReport(['members' => [$user->id]], $period);

        return response()->json($report->generate());
    }

    /**
     * @param Request $request
     * @param User $user
     * @param string $period
     * @return JsonResponse
     */
    public function userProjects(Request $request, User $user, string $period): JsonResponse
    {
        $report = new UserProjectsReport(['members' => [$user->id]], $period);

        return response()->json($report->generate());
    }

    /**
     * @param array $options
     * @return array
     */
    private function createFullReport(array $options): array
    {
        $report = new FullReport($options);
        return $report->generate();
    }
}
