<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use FilterDataTrait;

    /* ===========================
       SUMMARY
    ============================ */

    // public function summary(Request $request)
    // {
    //     $startDate = $request->filled('start_date')
    //         ? Carbon::parse($request->start_date)->startOfDay()
    //         : now()->startOfMonth();

    //     $endDate = $request->filled('end_date')
    //         ? Carbon::parse($request->end_date)->endOfDay()
    //         : now()->endOfMonth();

    //     /* ================= ACTIVE GUARDS ================= */
    //     $usersQuery = DB::table('users')->where('isActive', 1);

    //     // RANGE / BEAT filter via site_assign
    //     if ($request->filled('range') || $request->filled('beat')) {
    //         $usersQuery->join('site_assign', 'users.id', '=', 'site_assign.user_id');

    //         if ($request->filled('range')) {
    //             $usersQuery->where('site_assign.client_id', $request->range);
    //         }

    //         if ($request->filled('beat')) {
    //             $usersQuery->where('site_assign.site_id', $request->beat);
    //         }
    //     }

    //     $users = $usersQuery->pluck('users.id');
    //     $totalGuards = $users->count();

    //     /* ================= ATTENDANCE (FILTERED) ================= */
    //     $attendanceQuery = DB::table('attendance')
    //         ->whereBetween('dateFormat', [
    //             $startDate->toDateString(),
    //             $endDate->toDateString()
    //         ])
    //         ->whereIn('user_id', $users);

    //     if ($request->filled('compartment')) {
    //         $attendanceQuery->where('geo_id', $request->compartment);
    //     }

    //     $attendance = $attendanceQuery
    //         ->select('user_id', 'dateFormat')
    //         ->distinct()
    //         ->get();

    //     /* ================= KPI ================= */
    //     $present = $attendance->pluck('user_id')->unique()->count();
    //     $absent  = max(0, $totalGuards - $present);
    //     $total   = $totalGuards;

    //     /* ================= DAILY TREND ================= */
    //     $daily = collect();
    //     $cursor = $startDate->copy();

    //     while ($cursor <= $endDate) {
    //         $presentCount = $attendance
    //             ->where('dateFormat', $cursor->toDateString())
    //             ->pluck('user_id')
    //             ->unique()
    //             ->count();

    //         $daily->push([
    //             'date'    => $cursor->format('d M'),
    //             'present' => $presentCount,
    //             'absent'  => max(0, $totalGuards - $presentCount),
    //         ]);

    //         $cursor->addDay();
    //     }

    //     /* ================= TOP 10 ATTENDANCE ================= */
    //     $topAttendance = (clone $attendanceQuery)
    //         ->select('name', DB::raw('COUNT(DISTINCT dateFormat) as days_present'))
    //         ->groupBy('name')
    //         ->orderByDesc('days_present')
    //         ->limit(10)
    //         ->get();

    //     /* ================= TOP 10 DEFAULTERS ================= */
    //     $defaulters = DB::table('users')
    //         ->whereIn('users.id', $users)
    //         ->leftJoin('attendance', function ($join) use ($startDate, $endDate) {
    //             $join->on('users.id', '=', 'attendance.user_id')
    //                  ->whereBetween('attendance.dateFormat', [
    //                      $startDate->toDateString(),
    //                      $endDate->toDateString()
    //                  ]);
    //         })
    //         ->select(
    //             'users.name',
    //             DB::raw('COUNT(DISTINCT attendance.dateFormat) as days_present')
    //         )
    //         ->groupBy('users.id', 'users.name')
    //         ->orderBy('days_present')
    //         ->limit(10)
    //         ->get();

    //     /* ================= GUARD-WISE ================= */
    //     $guardAttendance = (clone $attendanceQuery)
    //         ->select('name', DB::raw('COUNT(DISTINCT dateFormat) as days_present'))
    //         ->groupBy('name')
    //         ->orderByDesc('days_present')
    //         ->get();

    //     return view('attendance.summary', compact(
    //         'present',
    //         'absent',
    //         'total',
    //         'daily',
    //         'topAttendance',
    //         'defaulters',
    //         'guardAttendance'
    //     ));
    // }

    // /* ===========================
    //    EXPLORER
    // ============================ */

    // public function explorer(Request $request)
    // {
    //     $month = $request->get('month', now()->format('Y-m'));
    //     $startDate = Carbon::parse($month . '-01');
    //     $endDate = $startDate->copy()->endOfMonth();
    //     $daysInMonth = $startDate->daysInMonth;

    //     /* ================= USERS ================= */
    //     $usersQuery = DB::table('users')
    //         ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
    //         ->where('users.isActive', 1);

    //     if ($request->filled('range')) {
    //         $usersQuery->where('site_assign.client_id', $request->range);
    //     }

    //     if ($request->filled('beat')) {
    //         $usersQuery->where('site_assign.site_id', $request->beat);
    //     }

    //     $users = $usersQuery
    //         ->select(
    //             'users.id',
    //             'users.name',
    //             'users.profile_pic',
    //             'site_assign.client_name as range',
    //             'site_assign.site_name as beat'
    //         )
    //         ->get()
    //         ->keyBy('id');

    //     /* ================= ATTENDANCE ================= */
    //     $attendanceQuery = DB::table('attendance')
    //         ->whereBetween('dateFormat', [
    //             $startDate->toDateString(),
    //             $endDate->toDateString()
    //         ])
    //         ->whereIn('user_id', $users->keys());

    //     if ($request->filled('compartment')) {
    //         $attendanceQuery->where('geo_id', $request->compartment);
    //     }

    //     $attendance = $attendanceQuery
    //         ->get()
    //         ->groupBy(fn ($r) => $r->user_id . '_' . $r->dateFormat);

    //     /* ================= GRID ================= */
    //     $grid = [];

    //     foreach ($users as $user) {
    //         $presentCount = 0;

    //         for ($d = 1; $d <= $daysInMonth; $d++) {
    //             $date = $startDate->copy()->day($d)->toDateString();
    //             $key = $user->id . '_' . $date;

    //             $present = isset($attendance[$key]);
    //             if ($present) $presentCount++;

    //             $grid[$user->id]['days'][$d] = compact('present');
    //         }

    //         $grid[$user->id]['user'] = $user;
    //         $grid[$user->id]['summary'] = [
    //             'present' => $presentCount,
    //             'total'   => $daysInMonth,
    //         ];
    //     }

    //     $totalPresent = collect($grid)->sum(fn ($g) => $g['summary']['present']);
    //     $totalDays = count($users) * $daysInMonth;
    //     $totalAbsent = $totalDays - $totalPresent;
    //     $presentPct = $totalDays
    //         ? round(($totalPresent / $totalDays) * 100, 2)
    //         : 0;

    //     $months = DB::table('attendance')
    //         ->selectRaw("DATE_FORMAT(dateFormat,'%Y-%m') as ym")
    //         ->distinct()
    //         ->orderByDesc('ym')
    //         ->pluck('ym');

    //     return view('attendance.explorer', array_merge(
    //         $this->filterData(),
    //         compact(
    //             'grid',
    //             'daysInMonth',
    //             'presentPct',
    //             'totalPresent',
    //             'totalAbsent',
    //             'totalDays',
    //             'months',
    //             'month'
    //         )
    //     ));
    // }

 public function summary(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfMonth();

        /* ============================================================
           USERS (SOURCE OF TRUTH → site_assign)
        ============================================================ */
        $usersQuery = DB::table('users')
            ->join('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.isActive', 1);

        if ($request->filled('range')) {
            $usersQuery->where('site_assign.client_id', $request->range);
        }

        if ($request->filled('beat')) {
            // site_id is TEXT (CSV)
            $usersQuery->whereRaw(
                "FIND_IN_SET(?, site_assign.site_id)",
                [$request->beat]
            );
        }

        $users = $usersQuery->pluck('users.id');
        $totalGuards = $users->count();

        /* ============================================================
           ATTENDANCE (ONLY PRESENCE LOGS)
        ============================================================ */
        $attendanceQuery = DB::table('attendance')
            ->whereBetween('dateFormat', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ])
            ->whereIn('user_id', $users);

        if ($request->filled('compartment')) {
            $attendanceQuery->whereIn('site_id', function ($q) use ($request) {
                $q->select('site_id')
                  ->from('site_geofences')
                  ->where('id', $request->compartment);
            });
        }

        $attendance = $attendanceQuery
            ->select('user_id', 'dateFormat')
            ->distinct()
            ->get();

        /* ============================================================
           KPI
        ============================================================ */
        $present = $attendance->pluck('user_id')->unique()->count();
        $absent  = max(0, $totalGuards - $present);
        $total   = $totalGuards;

        /* ============================================================
           DAILY TREND
        ============================================================ */
        $daily = collect();
        $cursor = $startDate->copy();

        while ($cursor <= $endDate) {
            $presentCount = $attendance
                ->where('dateFormat', $cursor->toDateString())
                ->pluck('user_id')
                ->unique()
                ->count();

            $daily->push([
                'date'    => $cursor->format('d M'),
                'present' => $presentCount,
                'absent'  => max(0, $totalGuards - $presentCount),
            ]);

            $cursor->addDay();
        }

        /* ============================================================
           TOP 10 ATTENDANCE
        ============================================================ */
        $topAttendance = (clone $attendanceQuery)
            ->join('users', 'users.id', '=', 'attendance.user_id')
            ->select(
                'users.name',
                DB::raw('COUNT(DISTINCT attendance.dateFormat) as days_present')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('days_present')
            ->limit(10)
            ->get();

        /* ============================================================
           TOP 10 DEFAULTERS
        ============================================================ */
        $defaulters = DB::table('users')
            ->whereIn('users.id', $users)
            ->leftJoin('attendance', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendance.user_id')
                     ->whereBetween('attendance.dateFormat', [
                         $startDate->toDateString(),
                         $endDate->toDateString()
                     ]);
            })
            ->select(
                'users.name',
                DB::raw('COUNT(DISTINCT attendance.dateFormat) as days_present')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('days_present')
            ->limit(10)
            ->get();

        /* ============================================================
           GUARD-WISE
        ============================================================ */
        $guardAttendance = (clone $attendanceQuery)
            ->join('users', 'users.id', '=', 'attendance.user_id')
            ->select(
                'users.name',
                DB::raw('COUNT(DISTINCT attendance.dateFormat) as days_present')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('days_present')
            ->get();

        return view('attendance.summary', compact(
            'present',
            'absent',
            'total',
            'daily',
            'topAttendance',
            'defaulters',
            'guardAttendance'
        ));
    }

    /* ============================================================
       EXPLORER
    ============================================================ */
    public function explorer(Request $request)
{
    $month = $request->get('month', now()->format('Y-m'));
    $startDate = Carbon::parse($month . '-01')->startOfDay();
    $endDate   = Carbon::parse($month . '-01')->endOfMonth()->endOfDay();
    $daysInMonth = $startDate->daysInMonth;

    /* ================= USERS + ASSIGN ================= */

    $users = DB::table('users')
        ->join('site_assign', 'users.id', '=', 'site_assign.user_id')
        ->where('users.isActive', 1)
        ->when($request->filled('range'), fn ($q) =>
            $q->where('site_assign.client_id', $request->range)
        )
        ->when($request->filled('beat'), fn ($q) =>
            $q->whereRaw('FIND_IN_SET(?, site_assign.site_id)', [$request->beat])
        )
        ->select(
            'users.id',
            'users.name',
            'users.profile_pic',
            'site_assign.client_name as range',
            'site_assign.site_id',
            'site_assign.site_name'
        )
        ->get()
        ->keyBy('id');

    if ($users->isEmpty()) {
        return view('attendance.explorer', array_merge(
            $this->filterData(),
            compact('users')
        ));
    }

    /* ================= BEAT MAP ================= */

    $userBeatMap = [];
    foreach ($users as $u) {
        $beatIds = array_filter(explode(',', $u->site_id));
        $userBeatMap[$u->id] = (int) ($request->beat ?? $beatIds[0] ?? null);
    }

    /* ================= COMPARTMENT MAP ================= */

    $compartmentMap = DB::table('site_geofences')
        ->whereIn('site_id', array_values($userBeatMap))
        ->orderBy('id')
        ->get()
        ->groupBy('site_id')
        ->map(fn ($rows) => $rows->first()->name);

    /* ================= ATTENDANCE ================= */

    $attendance = DB::table('attendance')
        ->whereBetween('dateFormat', [
            $startDate->toDateString(),
            $endDate->toDateString()
        ])
        ->whereIn('user_id', $users->keys())
        ->select('user_id', 'dateFormat')
        ->get()
        ->groupBy(fn ($r) => $r->user_id . '_' . $r->dateFormat);

    /* ================= GRID ================= */

    $grid = [];

    foreach ($users as $user) {

        $presentCount = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $startDate->copy()->day($d)->toDateString();
            $key = $user->id . '_' . $date;

            $present = isset($attendance[$key]);
            if ($present) $presentCount++;

            $grid[$user->id]['days'][$d] = compact('present');
        }

        $beatId = $userBeatMap[$user->id];

        $grid[$user->id]['user'] = $user;
        $grid[$user->id]['meta'] = [
            'range'       => $user->range ?? '-',
            'beat'        => $user->site_name ?? '-',
            'compartment' => $compartmentMap[$beatId] ?? '-',
        ];
        $grid[$user->id]['summary'] = [
            'present' => $presentCount,
            'total'   => $daysInMonth,
        ];
    }

    /* ================= KPIs ================= */

    $totalPresent = collect($grid)->sum(fn ($g) => $g['summary']['present']);
    $totalDays = count($users) * $daysInMonth;
    $totalAbsent = $totalDays - $totalPresent;
    $presentPct = $totalDays
        ? round(($totalPresent / $totalDays) * 100, 2)
        : 0;

    $months = DB::table('attendance')
        ->selectRaw("DATE_FORMAT(dateFormat,'%Y-%m') as ym")
        ->distinct()
        ->orderByDesc('ym')
        ->pluck('ym');

    return view('attendance.explorer', array_merge(
        $this->filterData(),
        compact(
            'grid',
            'daysInMonth',
            'presentPct',
            'totalPresent',
            'totalAbsent',
            'totalDays',
            'months',
            'month'
        )
    ));
}


}
