<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Traits\FilterDataTrait;

class PatrolController extends Controller
{
    use FilterDataTrait;

    /* =====================================================
       FOOT PATROL SUMMARY
    ====================================================== */
  public function footSummary(Request $request)
{
    $base = DB::table('patrol_sessions')
        ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
        ->leftJoin('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
        ->whereIn('patrol_sessions.session', ['Foot', 'Vehicle']);

    /* Date filter */
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $base->whereBetween('patrol_sessions.started_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ]);
    }
    if ($request->filled('range')) {
        $base->where('site_details.client_name', $request->range);
    }
    if ($request->filled('beat')) {
        $base->where('site_details.name', $request->beat);
    }
    /* KPIs */
    $totalSessions = (clone $base)->count();
    $completed     = (clone $base)->whereNotNull('patrol_sessions.ended_at')->count();
    $ongoing       = (clone $base)->whereNull('patrol_sessions.ended_at')->count();
$totalDistance = round(
    (clone $base)
        ->whereNotNull('patrol_sessions.ended_at')
        ->sum('patrol_sessions.distance') / 1000,
    2
);
    /* Guard-wise summary (paginated) */
    $guardStats = (clone $base)
        ->groupBy('users.id', 'users.name')
        ->selectRaw('
            users.name as guard,
            COUNT(*) as total_sessions,
            SUM(patrol_sessions.ended_at IS NOT NULL) as completed,
            SUM(patrol_sessions.ended_at IS NULL) as ongoing,
            ROUND(SUM(patrol_sessions.distance) / 1000, 2) as total_distance,ROUND(
    (SUM(patrol_sessions.distance) / 1000) /
    NULLIF(SUM(TIMESTAMPDIFF(MINUTE, patrol_sessions.started_at, patrol_sessions.ended_at)) / 60, 0),2) as km_per_hour')
        ->orderByDesc('total_distance')
        ->paginate(20)
        ->withQueryString();
    /* Range-wise distance (FIXED alias) */
   $rangeStats = (clone $base)
    ->whereNotNull('site_details.client_name')
    ->where('site_details.client_name', '!=', '')
    ->whereNotNull('patrol_sessions.ended_at')
    ->groupBy('site_details.client_name')
    ->selectRaw('
        site_details.client_name as range_name,
        ROUND(SUM(patrol_sessions.distance) / 1000, 2) as distance
    ')
    ->havingRaw('SUM(patrol_sessions.distance) > 0')
    ->orderByDesc('distance')
    ->get();

    /* Daily trend (last 30 days) */
    $dailyTrend = (clone $base)
    ->whereNotNull('patrol_sessions.ended_at')
    ->whereDate('patrol_sessions.started_at', '>=', now()->subDays(30))
    ->groupBy(DB::raw('DATE(patrol_sessions.started_at)'))
    ->selectRaw('
        DATE(patrol_sessions.started_at) as day,
        ROUND(SUM(patrol_sessions.distance) / 1000, 2) as distance
    ')
    ->orderBy('day')
    ->get();

    return view('patrol.foot-summary', array_merge(
        $this->filterData(),
        compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'guardStats',
            'rangeStats',
            'dailyTrend')
    ));
}

    /* =====================================================
       FOOT PATROL EXPLORER
    ====================================================== */
  public function footExplorer(Request $request)
{
    $q = DB::table('patrol_sessions')
        ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
        ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
        ->whereIn('patrol_sessions.session', ['Foot','Vehicle']);

    // Date filter
    if ($request->filled('start_date') && $request->filled('end_date')) {
    $q->whereBetween('patrol_sessions.started_at', [
        $request->start_date . ' 00:00:00',
        $request->end_date . ' 23:59:59'
    ]);}
 // Range filter
    if ($request->filled('range')) {
        $q->where('site_details.client_name', $request->range);
    }
    // Beat filter
    if ($request->filled('beat')) {
        $q->where('site_details.name', $request->beat);
    }

    $patrols = $q->select(
            'users.name as user_name',
            'site_details.client_name as range',
            'site_details.name as beat',
            'patrol_sessions.started_at',
            'patrol_sessions.ended_at',
DB::raw('ROUND(COALESCE(patrol_sessions.distance,0) / 1000, 2) as distance')
        )
        ->orderByDesc('patrol_sessions.started_at')
        ->paginate(25)
        ->withQueryString();

    return view('patrol.foot-explorer', array_merge(
        $this->filterData(),
        compact('patrols')
    ));
}


public function footDistanceByGuard(Request $request)
{
    $q = DB::table('patrol_sessions')
        ->join('users','users.id','=','patrol_sessions.user_id')
        ->leftJoin('site_details','site_details.id','=','patrol_sessions.site_id')
        ->whereIn('patrol_sessions.session', ['Foot','Vehicle'])
        ->whereNotNull('patrol_sessions.ended_at');

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $q->whereBetween('patrol_sessions.started_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ]);
    }

    if ($request->filled('range')) {
        $q->where('site_details.client_name', $request->range);
    }

    return $q->groupBy('users.id','users.name')
        ->selectRaw('
            users.name as guard,
            ROUND(SUM(patrol_sessions.distance)/ 1000,2) as total_distance
        ')
        ->orderByDesc('total_distance')
        ->get();
}




    /* =====================================================
       NIGHT PATROL SUMMARY
    ====================================================== */
   public function nightSummary(Request $request)
{
    $base = DB::table('patrol_sessions')
        ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
        ->whereIn('patrol_sessions.session', ['Foot','Vehicle'])
        ->where(function ($q) {
            $q->whereTime('patrol_sessions.started_at', '>=', '18:00:00')
              ->orWhereTime('patrol_sessions.started_at', '<', '06:00:00');
        });

    /* Date filter */
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $base->whereBetween('patrol_sessions.started_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ]);
    }

    /* ================= KPIs ================= */
    $totalSessions = (clone $base)->count();
    $completed     = (clone $base)->whereNotNull('patrol_sessions.ended_at')->count();
    $ongoing       = (clone $base)->whereNull('patrol_sessions.ended_at')->count();

    $totalDistance = round(
        (clone $base)
            ->whereNotNull('patrol_sessions.ended_at')
            ->sum('patrol_sessions.distance') / 1000,
        2
    );

    /* ================= GUARD TABLE ================= */
    $guardStats = (clone $base)
        ->groupBy('users.id','users.name')
        ->selectRaw('
            users.name as guard,
            COUNT(*) as total_sessions,
            SUM(patrol_sessions.ended_at IS NOT NULL) as completed,
            SUM(patrol_sessions.ended_at IS NULL) as ongoing,
            ROUND(SUM(patrol_sessions.distance)/1000,2) as total_distance,
            ROUND(
                (SUM(patrol_sessions.distance)/1000) /
                NULLIF(SUM(TIMESTAMPDIFF(MINUTE, patrol_sessions.started_at, patrol_sessions.ended_at))/60,0),
            2) as km_per_hour
        ')
        ->orderByDesc('total_distance')
        ->paginate(20)
        ->withQueryString();

    /* ================= TOP 5 GUARDS ================= */
    $topGuards = (clone $base)
        ->whereNotNull('patrol_sessions.ended_at')
        ->groupBy('users.id','users.name')
        ->selectRaw('
            users.name as guard,
            ROUND(SUM(patrol_sessions.distance)/1000,2) as distance
        ')
        ->orderByDesc('distance')
        ->limit(5)
        ->get();

    /* ================= SPEED ================= */
    $speedStats = (clone $base)
        ->whereNotNull('patrol_sessions.ended_at')
        ->groupBy('users.id','users.name')
        ->selectRaw('
            users.name as guard,
            ROUND(
                (SUM(patrol_sessions.distance)/1000) /
                NULLIF(SUM(TIMESTAMPDIFF(MINUTE, patrol_sessions.started_at, patrol_sessions.ended_at))/60,0),
            2) as speed
        ')
        ->orderByDesc('speed')
        ->get();

    $statusPie = [
        'completed'  => $completed,
        'ongoing'    => $ongoing,
        'incomplete' => max(0, $totalSessions - ($completed + $ongoing))
    ];

    return view('patrol.night-summary', array_merge(
        $this->filterData(),
        compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'guardStats',
            'topGuards',
            'speedStats',
            'statusPie'
        )
    ));
}



    /* =====================================================
       NIGHT PATROL EXPLORER
    ====================================================== */
 public function nightExplorer(Request $request)
{
    /* ================= BASE QUERY ================= */
    $base = DB::table('patrol_sessions')
        ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
        ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
        ->whereIn('patrol_sessions.session', ['Foot','Vehicle'])
        ->where(function ($q) {
            $q->whereTime('patrol_sessions.started_at', '>=', '18:00:00')
              ->orWhereTime('patrol_sessions.started_at', '<', '06:00:00');
        });

    /* ================= DATE FILTER ================= */
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $base->whereBetween('patrol_sessions.started_at', [
            $request->start_date.' 00:00:00',
            $request->end_date.' 23:59:59'
        ]);
    }

    /* ================= HEATMAP ================= */
    $nightHeatmap = (clone $base)
        ->whereNotNull('patrol_sessions.path_geojson')
        ->select('patrol_sessions.path_geojson')
        ->get();

    /* ================= KPIs ================= */
    $totalBeats = DB::table('site_details')
        ->whereNotNull('name')
        ->distinct('name')
        ->count('name');

    $patrolledBeats = (clone $base)
        ->whereNotNull('patrol_sessions.ended_at')
        ->distinct('site_details.name')
        ->count('site_details.name');

    $kpis = [
        'total_sessions' => (clone $base)->count(),
        'completed'      => (clone $base)->whereNotNull('ended_at')->count(),
        'ongoing'        => (clone $base)->whereNull('ended_at')->count(),
        'active_guards'  => (clone $base)->distinct('user_id')->count('user_id'),
        'total_distance' => round(
            (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
        2),
        'beats_covered_pct' => $totalBeats > 0
            ? round(($patrolledBeats / $totalBeats) * 100, 1)
            : 0
    ];

    /* ================= TABLE ================= */
    $patrols = (clone $base)
        ->select(
            'users.name as guard',
            'patrol_sessions.session as type',
            'patrol_sessions.started_at',
            'patrol_sessions.ended_at',
            DB::raw('ROUND(COALESCE(patrol_sessions.distance,0)/1000,2) as distance')
        )
        ->orderByDesc('patrol_sessions.started_at')
        ->paginate(25)
        ->withQueryString();

    /* ================= SPEED ================= */
    $speedStats = (clone $base)
        ->whereNotNull('ended_at')
        ->groupBy('users.id','users.name')
        ->selectRaw('
            users.name as guard,
            ROUND(
                (SUM(distance)/1000) /
                NULLIF(SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at))/60,0),
            2) as speed
        ')
        ->orderByDesc('speed')
        ->get();

    /* ================= TOTAL DISTANCE ================= */
    $nightDistanceByGuard = (clone $base)
        ->whereNotNull('ended_at')
        ->groupBy('users.id','users.name')
        ->selectRaw('
            users.name as guard,
            ROUND(SUM(distance)/1000,2) as total_distance
        ')
        ->orderByDesc('total_distance')
        ->get();

        $nightSessionsByGuard = (clone $base)
    ->groupBy('users.id','users.name')
    ->selectRaw('users.name as guard, COUNT(*) as sessions')
    ->orderByDesc('sessions')
    ->get();

    return view('patrol.night-explorer', array_merge(
        $this->filterData(),
        compact(
            'patrols',
            'kpis',
            'speedStats',
            'nightHeatmap',
            'nightDistanceByGuard'
        )
    ));
}



    /* =====================================================
       MAP VIEW
    ====================================================== */
 public function maps(Request $request)
{
    $base = DB::table('patrol_sessions')
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->whereNotNull('patrol_sessions.path_geojson');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $base->whereBetween('patrol_sessions.started_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('range')) {
            $base->where('site_details.client_name', $request->range);
        }

        if ($request->filled('beat') && Schema::hasColumn('site_details', 'beat')) {
            $base->where('site_details.beat', $request->beat);
        }

        if ($request->filled('compartment') && Schema::hasColumn('site_details', 'compartment')) {
            $base->where('site_details.compartment', $request->compartment);
        }

    /* PATHS */
    $paths = (clone $base)
        ->select(
            'patrol_sessions.user_id',
            'patrol_sessions.session',
            'patrol_sessions.path_geojson'
        )
        ->get()
        ->groupBy('user_id');

    /* GUARDS (PAGINATED) */
    $guards = (clone $base)
        ->groupBy('users.id', 'users.name', 'users.role_id')
        ->select(
            'users.id',
            'users.name',
            DB::raw("CASE WHEN users.role_id = 2 THEN 'Circle Incharge' ELSE 'Forest Guard' END AS designation")
        )
        ->orderBy('users.name')
        ->paginate(20)
        ->withQueryString();

    /* KPIs */
    $stats = [
        'total_guards' => $guards->total(),
        'active_patrols' => (clone $base)->whereNull('patrol_sessions.ended_at')->count(),
        'completed_patrols' => (clone $base)->whereNotNull('patrol_sessions.ended_at')->count(),
        'total_distance_km' => round(
            (clone $base)->whereNotNull('patrol_sessions.ended_at')->sum('patrol_sessions.distance') / 1000,
            2
        )
    ];

    /* GEOFENCES */
    $geofences = DB::table('site_geofences')
        ->whereNull('deleted_at')
        ->select('site_name', 'type', 'lat', 'lng', 'radius', 'poly_lat_lng')
        ->get();

    return view('patrol.maps', compact(
        'paths',
        'guards',
        'stats',
        'geofences'
    ));
}





}
