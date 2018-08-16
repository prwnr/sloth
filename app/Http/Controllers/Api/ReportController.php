<?php

namespace App\Http\Controllers\Api;

use App\Models\Date\CustomRange;
use App\Models\Date\DateRange;
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
        $report = new Report($this->getDateRange($request->input('range')));
        return response()->json($report->generate());
    }

    /**
     * @param $range
     * @return DateRange
     */
    private function getDateRange($range): DateRange
    {
        if (\is_array($range)) {
            $start = Carbon::createFromFormat(DateRange::FORMAT, $range['start']);
            $end = Carbon::createFromFormat(DateRange::FORMAT, $range['end']);
            return new DateRange(DateRange::CUSTOM, $start, $end);
        }

        return new DateRange($range, Carbon::now());
    }
}
