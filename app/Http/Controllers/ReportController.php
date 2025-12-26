<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyPatrolExport;
use App\Http\Controllers\Traits\FilterDataTrait;

class ReportController extends Controller
{
    use FilterDataTrait;

    /* ================= MONTHLY REPORT ================= */
   public function monthly(Request $request)
{
    $base = DB::table('patrol_sessions')
        ->whereNotNull('ended_at');

    if ($request->filled('year')) {
        $base->whereYear('started_at', $request->year);
    }

    $monthly = (clone $base)
        ->selectRaw('
            MONTH(started_at) as month,
            COUNT(*) as sessions,
            COUNT(DISTINCT user_id) as guards,
            ROUND(SUM(distance)/1000,2) as distance,
            ROUND(AVG(distance)/1000,2) as avg_distance
        ')
        ->groupByRaw('MONTH(started_at)')
        ->orderBy('month')
        ->get();

    $kpis = [
        'total_sessions'   => (clone $base)->count(),
        'total_guards'     => (clone $base)->distinct('user_id')->count('user_id'),
        'total_distance'   => round((clone $base)->sum('distance') / 1000, 2),
        'avg_per_session'  => round((clone $base)->avg('distance') / 1000, 2),
    ];

    return view('reports.monthly', compact('monthly', 'kpis'));
}


  /* ================= FOOT REPORT ================= */
    public function footReport(Request $request)
    {
        $base = DB::table('patrol_sessions')
            ->whereIn('session', ['Foot', 'Vehicle']);

        $totalSessions = (clone $base)->count();
        $completed     = (clone $base)->whereNotNull('ended_at')->count();
        $ongoing       = (clone $base)->whereNull('ended_at')->count();

        $totalDistance = round(
            (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
            2
        );

        $guardStats = (clone $base)
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('
                users.name as guard,
                COUNT(*) as total_sessions,
                SUM(patrol_sessions.ended_at IS NOT NULL) as completed,
                SUM(patrol_sessions.ended_at IS NULL) as ongoing,
                ROUND(SUM(distance)/1000,2) as total_distance,
                ROUND(
                    (SUM(distance)/1000) /
                    NULLIF(SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at))/60,0),
                2) as km_per_hour
            ')
            ->orderByDesc('total_distance')
            ->paginate(15);

        return view('reports.foot-report', compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'guardStats'
        ));
    }

    /* ================= NIGHT REPORT ================= */
    public function nightReport(Request $request)
    {
        $base = DB::table('patrol_sessions')
            ->whereIn('session', ['Foot','Vehicle'])
            ->where(function ($q) {
                $q->whereTime('started_at', '>=', '18:00:00')
                  ->orWhereTime('started_at', '<', '06:00:00');
            });

        $totalSessions = (clone $base)->count();
        $completed     = (clone $base)->whereNotNull('ended_at')->count();
        $ongoing       = (clone $base)->whereNull('ended_at')->count();

        $totalDistance = round(
            (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
            2
        );

        $topGuards = (clone $base)
            ->join('users','users.id','=','patrol_sessions.user_id')
            ->whereNotNull('ended_at')
            ->groupBy('users.id','users.name')
            ->selectRaw('
                users.name as guard,
                ROUND(SUM(distance)/1000,2) as distance
            ')
            ->orderByDesc('distance')
            ->limit(5)
            ->get();

        return view('reports.night-report', compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'topGuards'
        ));
    }


    /* ================= CAMERA TRACKING ================= */
   public function cameraTracking(Request $request)
{
    $base = DB::table('patrol_sessions')
        ->join('users', 'users.id', '=', 'patrol_sessions.user_id');

    $stats = [
        'total_guards' => DB::table('users')->count(),
        'active_patrols' => (clone $base)->whereNull('ended_at')->count(),
        'completed_patrols' => (clone $base)->whereNotNull('ended_at')->count(),
        'total_distance_km' => round(
            (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
            2
        )
    ];

    $guards = (clone $base)
        ->groupBy('users.id', 'users.name', 'users.role_id')
        ->select(
            'users.name',
            DB::raw("
                CASE 
                    WHEN users.role_id = 2 THEN 'Circle Incharge'
                    ELSE 'Forest Guard'
                END as designation
            ")
        )
        ->orderBy('users.name')
        ->get();

    return view('reports.camera-tracking', compact('stats', 'guards'));
}

}
