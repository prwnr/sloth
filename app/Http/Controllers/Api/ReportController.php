<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\CustomRange;
use App\Models\Report\OveralReport;
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
        return response()->json($this->createReport($request->input('filters')));
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

        return response()->json($this->createReport($options));
    }

    /**
     * @param array $options
     * @return array
     */
    private function createReport(array $options): array
    {
        $report = new OveralReport();
        $report->addFilters($options);
        return $report->generate();
    }
}
