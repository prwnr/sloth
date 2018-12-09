<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\CustomRange;
use App\Models\Report\{Periodic\HoursReport, FullReport, Periodic\SalesReport, Periodic\UserProjectsReport};
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
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $options = $request->input('filters');
        $options['members'] = [$id];

        return response()->json($this->createFullReport($options));
    }

    /**
     * @param int $id
     * @param string $period
     * @return JsonResponse
     */
    public function userHours(int $id, string $period): JsonResponse
    {
        $report = new HoursReport(['members' => [$id]], $period);

        return response()->json($report->generate());
    }

    /**
     * @param int $id
     * @param string $period
     * @return JsonResponse
     */
    public function userProjects(int $id, string $period): JsonResponse
    {
        $report = new UserProjectsReport(['members' => [$id]], $period);

        return response()->json($report->generate());
    }

    /**
     * @param string $period
     * @return JsonResponse
     */
    public function sales(string $period): JsonResponse
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
