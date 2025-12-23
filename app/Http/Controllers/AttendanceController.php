<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FilterDataTrait;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use FilterDataTrait;

    public function summary(Request $request)
    {
        $baseQuery = DB::table('attendance')
            ->join('users', 'attendance.user_id', '=', 'users.id')
            ->join('site_details', 'attendance.site_id', '=', 'site_details.id');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $baseQuery->whereBetween('attendance.dateFormat', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->filled('range')) {
            $baseQuery->where('site_details.client_name', $request->range);
        }

        if ($request->filled('beat')) {
            $baseQuery->where('site_details.name', $request->beat);
        }

        if ($request->filled('geofence')) {
            $baseQuery->where('attendance.geo_name', $request->geofence);
        }

        $records = (clone $baseQuery)->select('attendance.attendance_flag')->get();

        $present = $records->where('attendance_flag', 1)->count();
        $absent  = $records->where('attendance_flag', 0)->count();
        $total   = $records->count();

        $daily = (clone $baseQuery)
            ->selectRaw("
                attendance.dateFormat as date,
                SUM(attendance_flag = 1) as present,
                SUM(attendance_flag = 0) as absent
            ")
            ->groupBy('attendance.dateFormat')
            ->orderBy('attendance.dateFormat')
            ->get();

        return view('attendance.summary', array_merge(
            $this->filterData(),
            compact('present', 'absent', 'total', 'daily')
        ));
    }

public function explorer(Request $request)
{
    $month = $request->get('month', now()->format('Y-m'));
    $startDate = \Carbon\Carbon::parse($month . '-01');
    $endDate = (clone $startDate)->endOfMonth();

    /* ---------------------------------
       1. BASE USERS (ACTIVE)
    ---------------------------------- */
    $usersQuery = DB::table('users')
        ->where('users.isActive', 1)
        ->select(
            'users.id',
            'users.name as user_name',
            'users.profile_pic'
        );

    /* ---------------------------------
       FILTERS (OPTIONAL)
    ---------------------------------- */
    if ($request->filled('range')) {
        $usersQuery->join('site_assign', 'users.id', '=', 'site_assign.user_id')
                   ->where('site_assign.client_name', $request->range);
    }

    if ($request->filled('beat')) {
        $usersQuery->join('site_assign as sa2', 'users.id', '=', 'sa2.user_id')
                   ->where('sa2.site_name', $request->beat);
    }

    $users = $usersQuery->distinct()->get();

    /* ---------------------------------
       2. ATTENDANCE MAP (FAST LOOKUP)
    ---------------------------------- */
    $attendance = DB::table('attendance')
        ->whereBetween('dateFormat', [$startDate, $endDate])
        ->select('user_id', 'dateFormat', 'geo_name', 'photo')
        ->get()
        ->groupBy(fn($a) => $a->user_id . '_' . $a->dateFormat);

    /* ---------------------------------
       3. BUILD GRID (User × Day)
    ---------------------------------- */
    $daysInMonth = $startDate->daysInMonth;

    $grid = [];

foreach ($users as $user) {

    /* ---- Always initialize structure ---- */
    $grid[$user->id] = [
        'user' => $user,
        'meta' => [
            'range' => $user->range ?? '-',
            'beat' => $user->beat ?? '-',
            'compartment' => '-',
        ],
        'summary' => [
            'present' => 0,
            'total' => $daysInMonth
        ],
        'days' => []
    ];

    /* ---- Day-wise attendance ---- */
    for ($d = 1; $d <= $daysInMonth; $d++) {

        $date = $startDate->copy()->day($d)->toDateString();
        $key = $user->id . '_' . $date;

        if (isset($attendance[$key])) {
            $grid[$user->id]['days'][$d] = [
                'present' => true
            ];

            $grid[$user->id]['summary']['present']++;

            // Capture first available compartment
            if ($grid[$user->id]['meta']['compartment'] === '-') {
                $grid[$user->id]['meta']['compartment'] =
                    $attendance[$key][0]->geo_name ?? '-';
            }

        } else {
            $grid[$user->id]['days'][$d] = [
                'present' => false
            ];
        }
    }
}


    /* ---------------------------------
       KPIs
    ---------------------------------- */
    $totalPresent = collect($grid)->sum(fn($u) => $u['summary']['present']);
    $totalDays = count($users) * $daysInMonth;
    $totalAbsent = $totalDays - $totalPresent;
    $presentPct = $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 2) : 0;

    /* ---------------------------------
       MONTH LIST
    ---------------------------------- */
    $months = DB::table('attendance')
        ->selectRaw("DATE_FORMAT(dateFormat,'%Y-%m') as ym")
        ->distinct()
        ->orderByDesc('ym')
        ->pluck('ym');

    return view('attendance.explorer', array_merge(
        $this->filterData(),
        compact(
            'grid',
            'presentPct',
            'totalPresent',
            'totalAbsent',
            'totalDays',
            'months',
            'month',
            'startDate'
        )
    ));
}



}
