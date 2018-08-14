<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\DateRangeFactory;
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
     * @var DateRangeFactory
     */
    private $rangeFactory;

    /**
     * ReportController constructor.
     * @param DateRangeFactory $rangeFactory
     */
    public function __construct(DateRangeFactory $rangeFactory)
    {
        $this->rangeFactory = $rangeFactory;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $dateRange = $this->rangeFactory->make($request->input('range'));
        $report = new Report($dateRange);
        return response()->json($report->generate());
    }
}
