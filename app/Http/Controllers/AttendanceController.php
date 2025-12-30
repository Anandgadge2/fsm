<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    use FilterDataTrait;

 
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

       $this->applyCanonicalFilters(
    $usersQuery,
    'attendance.dateFormat' // date column
);

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

       $this->applyCanonicalFilters(
    $attendanceQuery,
    'attendance.dateFormat' // date column
);
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
        // Prioritize explicit start/end dates from filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
        } else {
            // Default to current month if no filters
            $month = $request->get('month', now()->format('Y-m'));
            $startDate = Carbon::parse($month . '-01')->startOfDay();
            $endDate   = Carbon::parse($month . '-01')->endOfMonth()->endOfDay();
        }

        // Generate Dates Array for Column Headers
        $datePeriod = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        foreach ($datePeriod as $d) {
            $dates[] = $d->copy();
        }
        $totalDaysInRange = count($dates);

        /* ================= USERS + ASSIGN ================= */

        // Use standard filter resolution (Range -> Beat -> User)
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        $usersQuery = DB::table('users')
            ->join('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.isActive', 1);

        if ($request->filled('range')) {
            $usersQuery->where('site_assign.client_id', $request->range);
        }
        
        // Handle CSV site_id in site_assign
        if (!empty($siteIds)) {
            $usersQuery->where(function ($q) use ($siteIds) {
                foreach ($siteIds as $sid) {
                    $q->orWhereRaw('FIND_IN_SET(?, site_assign.site_id)', [$sid]);
                }
            });
        }
        
        if ($userId) {
            $usersQuery->where('users.id', $userId);
        }

        $users = $usersQuery
            ->select(
                'users.id',
                'users.name',
                'users.profile_pic',
                'site_assign.client_name as range',
                'site_assign.site_id',
                'site_assign.site_name'
            )
            ->distinct('users.id') // prevent duplicates if joined multiple times
            ->get()
            ->keyBy('id');

        if ($users->isEmpty()) {
            return view('attendance.explorer', array_merge(
                $this->filterData(),
                [
                    'users' => $users,
                    'dates' => $dates,
                    'totalDaysInRange' => $totalDaysInRange,
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]
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
            $dayData = [];

            // Iterate over generated dates
            foreach ($dates as $dt) {
                $dateStr = $dt->toDateString();
                $key = $user->id . '_' . $dateStr;

                $present = isset($attendance[$key]);
                if ($present) $presentCount++;

                $dayData[$dateStr] = compact('present');
            }

            $beatId = $userBeatMap[$user->id];

            $grid[$user->id]['user'] = $user;
            $grid[$user->id]['meta'] = [
                'range'       => $user->range ?? '-',
                'beat'        => $user->site_name ?? '-',
                'compartment' => $compartmentMap[$beatId] ?? '-',
            ];
            $grid[$user->id]['days'] = $dayData;
            $grid[$user->id]['summary'] = [
                'present' => $presentCount,
                'total'   => $totalDaysInRange,
            ];
        }

        /* ================= KPIs ================= */

        $totalPresent = collect($grid)->sum(fn ($g) => $g['summary']['present']);
        $totalPossibleDays = count($users) * $totalDaysInRange;
        $totalAbsent = $totalPossibleDays - $totalPresent;
        $presentPct = $totalPossibleDays > 0
            ? round(($totalPresent / $totalPossibleDays) * 100, 2)
            : 0;

        $months = DB::table('attendance')
            ->selectRaw("DATE_FORMAT(dateFormat,'%Y-%m') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym');

        return view('attendance.explorer', array_merge(
            $this->filterData(),
            [
                'grid' => $grid,
                'dates' => $dates,
                'totalDaysInRange' => $totalDaysInRange,
                'presentPct' => $presentPct,
                'totalPresent' => $totalPresent,
                'totalAbsent' => $totalAbsent,
                'totalDays' => $totalPossibleDays,
                'months' => $months,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]
        ));
    }


}
