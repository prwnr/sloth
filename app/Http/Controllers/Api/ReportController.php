<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\CustomRange;
use App\Models\Report\{Periodic\HoursReport, FullReport, Periodic\SalesReport, Periodic\UserProjectsReport};
use App\Models\Team\Member;
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
     * @param Member $member
     * @return JsonResponse
     */
    public function show(Request $request, Member $member): JsonResponse
    {
        $options = $request->input('filters');
        $options['members'] = [$member->id];

        return response()->json($this->createFullReport($options));
    }

    /**
     * @param Request $request
     * @param Member $member
     * @param string $period
     * @return JsonResponse
     */
    public function userHours(Request $request, Member $member, string $period): JsonResponse
    {
        $report = new HoursReport(['members' => [$member->id]], $period);

        return response()->json($report->generate());
    }

    /**
     * @param Request $request
     * @param Member $member
     * @param string $period
     * @return JsonResponse
     */
    public function userProjects(Request $request, Member $member, string $period): JsonResponse
    {
        $report = new UserProjectsReport(['members' => [$member->id]], $period);

        return response()->json($report->generate());
    }

    /**
     * @param Request $request
     * @param string $period
     * @return JsonResponse
     */
    public function sales(Request $request, string $period): JsonResponse
    {
        $report = new SalesReport([], $period);

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
