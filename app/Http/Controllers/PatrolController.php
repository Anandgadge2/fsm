<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FilterDataTrait;

class PatrolController extends Controller
{
    use FilterDataTrait;

    /* =========================
       FOOT PATROL SUMMARY
    ========================== */
  public function footSummary(Request $request)
{
    $query = DB::table('patrol_sessions')
        ->where('session', 'Foot');

    /* Date filter */
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('started_at', [
            $request->start_date,
            $request->end_date
        ]);
    }

    /* KPIs */
    $totalPatrols = (clone $query)->count();

    $totalDistance = round(
        (clone $query)->sum('distance'),
        2
    );

    $totalOfficers = (clone $query)
        ->distinct('user_id')
        ->count('user_id');

    return view('patrol.foot-summary', array_merge(
        $this->filterData(),
        compact(
            'totalPatrols',
            'totalDistance',
            'totalOfficers'
        )
    ));
}


    /* =========================
       FOOT PATROL EXPLORER
    ========================== */
    public function footExplorer(Request $request)
    {
        $query = DB::table('patrol_sessions')
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->leftJoin('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->where('patrol_sessions.session', 'Foot');

        $patrols = $query
            ->select(
                'patrol_sessions.id',
                'users.name as user_name',
                'site_details.client_name as range',
                'site_details.name as beat',
                'patrol_sessions.started_at',
                'patrol_sessions.ended_at',
                'patrol_sessions.distance',
                'patrol_sessions.path_geojson'
            )
            ->orderByDesc('patrol_sessions.started_at')
            ->paginate(50);

        return view('patrol.foot-explorer', array_merge(
            $this->filterData(),
            compact('patrols')
        ));
    }

    /* =========================
       NIGHT PATROL SUMMARY ✅ FIXED
    ========================== */
    public function nightSummary(Request $request)
    {
        $query = DB::table('patrol_sessions')
            ->where('type', 'Night');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('started_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $totalPatrols = $query->count();
        $totalDistance = round($query->sum('distance'), 2);

        return view('patrol.night-summary', array_merge(
            $this->filterData(),
            compact('totalPatrols', 'totalDistance')
        ));
    }

    /* =========================
       NIGHT PATROL EXPLORER
    ========================== */
    public function nightExplorer()
    {
        $patrols = DB::table('patrol_sessions')
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->where('patrol_sessions.type', 'Night')
            ->orderByDesc('started_at')
            ->limit(100)
            ->get();

        return view('patrol.night-explorer', array_merge(
            $this->filterData(),
            compact('patrols')
        ));
    }

    /* =========================
       MAP VIEW
    ========================== */
    public function maps()
    {
        $paths = DB::table('patrol_sessions')
            ->whereNotNull('path_geojson')
            ->get();

        return view('patrol.maps', array_merge(
            $this->filterData(),
            compact('paths')
        ));
    }
}
