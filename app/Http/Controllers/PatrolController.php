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

    $this->applyCanonicalFilters(
    $base,
    'patrol_sessions.started_at' // date column
);
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
            users.id as user_id,
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
    $base = DB::table('patrol_sessions')
        ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
        ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
        ->whereIn('patrol_sessions.session', ['Foot','Vehicle']);

    $this->applyCanonicalFilters(
    $base,
    'patrol_sessions.started_at' // date column
);

    $patrols = $base->select(
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
    $base = DB::table('patrol_sessions')
        ->join('users','users.id','=','patrol_sessions.user_id')
        ->leftJoin('site_details','site_details.id','=','patrol_sessions.site_id')
        ->whereIn('patrol_sessions.session', ['Foot','Vehicle'])
        ->whereNotNull('patrol_sessions.ended_at');

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $base->whereBetween('patrol_sessions.started_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ]);
    }

    $this->applyCanonicalFilters(
    $base,
    'patrol_sessions.started_at' // date column
);

    return $base->groupBy('users.id','users.name')
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
        ->where(function ($base) {
            $base->whereTime('patrol_sessions.started_at', '>=', '18:00:00')
              ->orWhereTime('patrol_sessions.started_at', '<', '06:00:00');
        });

    /* Date filter */
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $base->whereBetween('patrol_sessions.started_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ]);
    }
    $this->applyCanonicalFilters(
    $base,
    'patrol_sessions.started_at' // date column
);

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
            users.id as user_id,
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


    return view('patrol.night-summary', array_merge(
        $this->filterData(),
        compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'guardStats',
            'speedStats',

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
   $this->applyCanonicalFilters(
    $base,
    'patrol_sessions.started_at' // date column
);

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
       API: Get Session Details
    ====================================================== */
    public function getSessionDetails($sessionId)
    {
        $session = DB::table('patrol_sessions')
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->where('patrol_sessions.id', $sessionId)
            ->select(
                'patrol_sessions.id as session_id',
                'patrol_sessions.user_id',
                'patrol_sessions.site_id',
                'users.name as user_name',
                'users.profile_pic as user_profile',
                'users.contact as user_contact',
                'site_details.name as site_name',
                'site_details.client_name as range_name',
                'site_details.address as site_address',
                'patrol_sessions.type',
                'patrol_sessions.session',
                'patrol_sessions.method',
                'patrol_sessions.started_at',
                'patrol_sessions.ended_at',
                'patrol_sessions.start_lat',
                'patrol_sessions.start_lng',
                'patrol_sessions.end_lat',
                'patrol_sessions.end_lng',
                'patrol_sessions.path_geojson',
                'patrol_sessions.distance',
                DB::raw("CASE 
                    WHEN patrol_sessions.ended_at IS NULL THEN 'In Progress'
                    WHEN patrol_sessions.ended_at IS NOT NULL THEN 'Completed'
                    ELSE 'Unknown'
                END as status"),
                DB::raw("ROUND(COALESCE(patrol_sessions.distance, 0) / 1000, 2) as distance_km"),
                DB::raw("TIMESTAMPDIFF(MINUTE, patrol_sessions.started_at, COALESCE(patrol_sessions.ended_at, NOW())) as duration_minutes")
            )
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        // Get patrol logs for this session
        $logs = DB::table('patrol_logs')
            ->where('patrol_session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'session' => $session,
            'logs' => $logs
        ]);
    }

  
    /* =====================================================
       GUARD DETAILS VIEW
    ====================================================== */
    public function guardDetails($userId, Request $request)
    {
        // Get guard basic information
        $guard = DB::table('users')
            ->where('id', $userId)
            ->where('isActive', 1)
            ->first();

        if (!$guard) {
            abort(404, 'Guard not found');
        }

        // Apply filters to base query
        $base = DB::table('patrol_sessions')
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->where('patrol_sessions.user_id', $userId)
            ->whereNotNull('patrol_sessions.started_at');

        $this->applyCanonicalFilters(
            $base,
            'patrol_sessions.started_at'
        );

        // Get guard's patrol sessions
        $sessions = $base->select(
                'patrol_sessions.id as session_id',
                'patrol_sessions.user_id',
                'patrol_sessions.site_id',
                'users.name as user_name',
                'users.profile_pic as user_profile',
                'users.contact as user_contact',
                'users.designation as user_designation',
                'site_details.name as site_name',
                'site_details.client_name as range_name',
                'site_details.address as site_address',
                'patrol_sessions.type',
                'patrol_sessions.session',
                'patrol_sessions.method',
                'patrol_sessions.started_at',
                'patrol_sessions.ended_at',
                'patrol_sessions.start_lat',
                'patrol_sessions.start_lng',
                'patrol_sessions.end_lat',
                'patrol_sessions.end_lng',
                'patrol_sessions.path_geojson',
                'patrol_sessions.distance',
                DB::raw("CASE 
                    WHEN patrol_sessions.ended_at IS NULL THEN 'In Progress'
                    WHEN patrol_sessions.ended_at IS NOT NULL THEN 'Completed'
                    ELSE 'Unknown'
                END as status"),
                DB::raw("ROUND(COALESCE(patrol_sessions.distance, 0) / 1000, 2) as distance_km"),
                DB::raw("TIMESTAMPDIFF(MINUTE, patrol_sessions.started_at, COALESCE(patrol_sessions.ended_at, NOW())) as duration_minutes")
            )
            ->orderByDesc('patrol_sessions.started_at')
            ->paginate(20)
            ->withQueryString();





            
        // Get guard's assigned sites
        $assignedSites = DB::table('site_assign')
            ->leftJoin('site_details', 'site_details.id', '=', 'site_assign.site_id')
            ->leftJoin('client_details', 'client_details.id', '=', 'site_assign.client_id')
            ->where('site_assign.user_id', $userId)
            ->where('site_assign.end_date', '>=', date('Y-m-d'))
            ->select(
                'site_details.name as site_name',
                'site_details.address as site_address',
                'client_details.name as client_name',
                'site_assign.shift_name',
                'site_assign.start_date',
                'site_assign.end_date'
            )
            ->get();

        // Get guard's region geofences
        $guardRegions = DB::table('site_geofences')
            ->leftJoin('site_details', 'site_details.id', '=', 'site_geofences.site_id')
            ->whereIn('site_geofences.site_id', function($query) use ($userId) {
                $query->select('site_id')
                    ->from('site_assign')
                    ->where('user_id', $userId)
                    ->where('end_date', '>=', date('Y-m-d'));
            })
            ->select(
                'site_geofences.*',
                'site_details.name as site_name'
            )
            ->get();

        // Get patrol logs for this guard
        $patrolLogs = DB::table('patrol_logs')
            ->join('patrol_sessions', 'patrol_sessions.id', '=', 'patrol_logs.patrol_session_id')
            ->where('patrol_sessions.user_id', $userId)
            ->orderBy('patrol_logs.created_at', 'desc')
            ->limit(50)
            ->get();

        // Calculate guard statistics
        $stats = [
            'total_sessions' => (clone $base)->count(),
            'completed_sessions' => (clone $base)->whereNotNull('patrol_sessions.ended_at')->count(),
            'active_sessions' => (clone $base)->whereNull('patrol_sessions.ended_at')->count(),
            'total_distance_km' => round(
                (clone $base)->whereNotNull('patrol_sessions.ended_at')->sum('patrol_sessions.distance') / 1000,
                2
            ),
            'total_patrol_hours' => round(
                (clone $base)->whereNotNull('patrol_sessions.ended_at')
                    ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) / 60 as total_hours')
                    ->value('total_hours') ?: 0,
                2
            ),
            'avg_session_duration' => round(
                (clone $base)->whereNotNull('patrol_sessions.ended_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) / 60 as avg_duration')
                    ->value('avg_duration') ?: 0,
                2
            ),
            'sites_covered' => (clone $base)->distinct('patrol_sessions.site_id')->count('patrol_sessions.site_id')
        ];

        return view('patrol.guard-details', array_merge(
            $this->filterData(),
            compact(
                'guard', 
                'sessions', 
                'assignedSites', 
                'guardRegions', 
                'patrolLogs', 
                'stats'
            )
        ));
    }

    /* =====================================================
       KML/PATROL VIEW WITH SESSIONS
    ====================================================== */
    public function kmlView(Request $request)
    {
        $base = DB::table('patrol_sessions')
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->whereNotNull('patrol_sessions.path_geojson')
            ->whereNotNull('patrol_sessions.started_at');

        // Apply global filters
        $this->applyCanonicalFilters(
            $base,
            'patrol_sessions.started_at'
        );


        // Get patrol sessions with all details
        $sessions = $base->select(
                'patrol_sessions.id as session_id',
                'patrol_sessions.user_id',
                'patrol_sessions.site_id',
                'users.name as user_name',
                'users.profile_pic as user_profile',
                'site_details.name as site_name',
                'site_details.client_name as range_name',
                'patrol_sessions.type',
                'patrol_sessions.session',
                'patrol_sessions.started_at',
                'patrol_sessions.ended_at',
                'patrol_sessions.start_lat',
                'patrol_sessions.start_lng',
                'patrol_sessions.end_lat',
                'patrol_sessions.end_lng',
                'patrol_sessions.path_geojson',
                'patrol_sessions.distance',
                DB::raw("CASE 
                    WHEN patrol_sessions.ended_at IS NULL THEN 'In Progress'
                    WHEN patrol_sessions.ended_at IS NOT NULL THEN 'Completed'
                    ELSE 'Unknown'
                END as status"),
                DB::raw("ROUND(COALESCE(patrol_sessions.distance, 0) / 1000, 2) as distance_km")
            )
            ->orderByDesc('patrol_sessions.started_at')
            ->paginate(50)
            ->withQueryString();

        // Get unique users for filter dropdown
    //   $users = DB::table('users')
    // ->where('isActive', 1)
    // ->select('id', 'name')
    // ->orderBy('name')
    // ->get();
$users = (clone $base)
->reorder()
    ->select(
        'users.id',
        'users.name'
    )
    ->distinct()
    ->orderBy('users.name')
    ->get();


        // Get geofences for guard regions
        $geofences = DB::table('site_geofences')
            ->leftJoin('site_details', 'site_details.id', '=', 'site_geofences.site_id')
            ->whereNull('site_geofences.deleted_at')
            ->select(
                'site_geofences.*',
                'site_details.name as site_name',
                'site_details.client_name as range_name'
            )
            ->get();

        // KPIs
        $stats = [
            'total_sessions' => (clone $base)->count(),
            'completed_sessions' => (clone $base)->whereNotNull('patrol_sessions.ended_at')->count(),
            'active_sessions' => (clone $base)->whereNull('patrol_sessions.ended_at')->count(),
            'total_distance_km' => round(
                (clone $base)->whereNotNull('patrol_sessions.ended_at')->sum('patrol_sessions.distance') / 1000,
                2
            ),
            'unique_guards' => (clone $base)->distinct('patrol_sessions.user_id')->count('patrol_sessions.user_id'),
            'total_regions' => $geofences->count(),
            'active_regions' => $geofences->where('site_name', '!=', null)->count()
        ];

        return view('patrol.kml-view', array_merge(
            $this->filterData(),
            compact('sessions', 'users', 'stats', 'geofences')
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


            // Filter by guard
if ($request->filled('user_id')) {
    $base->where('patrol_sessions.user_id', $request->user_id);
}

// Sort by distance
if ($request->filled('sort') && $request->sort === 'distance_desc') {
    $base->orderByDesc('patrol_sessions.distance');
}

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $base->whereBetween('patrol_sessions.started_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

       $this->applyCanonicalFilters(
    $base,
    'patrol_sessions.started_at' // date column
);

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

    //     $users = DB::table('users')
    // ->join('patrol_sessions', 'patrol_sessions.user_id', '=', 'users.id')
    // ->select('users.id', 'users.name')
    // ->distinct()
    // ->orderBy('users.name')
    // ->get();

$users = (clone $base)
->reorder() // <-- THIS IS THE KEY
    ->select('users.id', 'users.name')
    ->distinct()
    ->orderBy('users.name')
    ->get();

    return view('patrol.maps', compact(
    'paths',
    'guards',
    'stats',
    'geofences',
    'users'
));

}





}
