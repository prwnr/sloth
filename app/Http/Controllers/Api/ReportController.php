<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\CustomRange;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $options = $request->input('filters');

        $filters = new Report\Filters($options);
        $report = new Report();
        $report->apply($filters);

        return response()->json($report->generate());
    }
}
