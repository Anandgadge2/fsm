<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function dashboard(Request $request)
    {
        $filters = [
            'from' => $request->start_date,
            'to'   => $request->end_date,
        ];

        $data = $this->analytics->getDashboardData($filters);

        return view('analytics.dashboard', [
            'hideFilters' => true,
            'data' => $data
        ]);
    }

    public function beatsByRange(Request $request)
{
    return DB::table('site_details')
        ->where('client_name', $request->range)
        ->select('name')
        ->distinct()
        ->orderBy('name')
        ->get();
}

public function geofencesByBeat(Request $request)
{
    return DB::table('attendance')
        ->where('site_name', $request->beat)
        ->whereNotNull('geo_name')
        ->select('geo_name')
        ->distinct()
        ->orderBy('geo_name')
        ->get();
}

}
