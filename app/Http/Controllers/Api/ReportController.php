<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\WeekRange;
use App\Models\Report;
use Carbon\Carbon;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $dateRange = new WeekRange(Carbon::now());
        $report = new Report($dateRange);
        return response()->json($report->generate());
    }
}
