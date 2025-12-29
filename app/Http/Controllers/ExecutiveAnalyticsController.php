<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Traits\FilterDataTrait;
use App\Helpers\FormatHelper;

class ExecutiveAnalyticsController extends Controller
{
    use FilterDataTrait;

    public function executiveDashboard(Request $request)
    {
        // Default date range: last 30 days
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : Carbon::now();

        return view('analytics.executive-dashboard', array_merge(
            $this->filterData(),
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'kpis' => $this->getKPIs($startDate, $endDate),
                'guardPerformance' => $this->getGuardPerformanceRankings($startDate, $endDate),
                'incidentTracking' => $this->getIncidentStatusTracking($startDate, $endDate),
                'patrolAnalytics' => $this->getPatrolAnalytics($startDate, $endDate),
                'attendanceAnalytics' => $this->getAttendanceAnalytics($startDate, $endDate),
                'timePatterns' => $this->getTimeBasedPatterns($startDate, $endDate),
                'riskZones' => $this->getRiskZoneAnalysis($startDate, $endDate),
                'coverageAnalysis' => $this->getCoverageAnalysis($startDate, $endDate),
                'efficiencyMetrics' => $this->getEfficiencyMetrics($startDate, $endDate),
            ]
        ));
    }

    private function getKPIs(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        // Active Guards
        $activeGuards = DB::table('users')
            ->where('isActive', 1)
            ->count();

        // Patrols
        $patrolQuery = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate]);
        
        if (!empty($siteIds)) {
            $patrolQuery->whereIn('site_id', $siteIds);
        }

        $totalPatrols = (clone $patrolQuery)->count();
        $completedPatrols = (clone $patrolQuery)->whereNotNull('ended_at')->count();
        $ongoingPatrols = $totalPatrols - $completedPatrols;

        // Distance
        $totalDistance = round((clone $patrolQuery)->whereNotNull('ended_at')->sum('distance') / 1000, 2);
        $avgDistancePerGuard = $activeGuards > 0 ? round($totalDistance / $activeGuards, 2) : 0;

        // Attendance
        $attendanceQuery = DB::table('attendance')
            ->whereBetween('dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        
        if (!empty($siteIds)) {
            $attendanceQuery->whereIn('site_id', $siteIds);
        }

        $totalAttendance = (clone $attendanceQuery)->count();
        $presentCount = (clone $attendanceQuery)->where('attendance_flag', 1)->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        // Incidents
        $incidentQuery = DB::table('incidence_details')
            ->whereBetween('dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        
        if (!empty($siteIds)) {
            $incidentQuery->whereIn('site_id', $siteIds);
        }

        $totalIncidents = (clone $incidentQuery)->count();
        $pendingIncidents = (clone $incidentQuery)
            ->whereIn('statusFlag', [0, 3, 4]) // pendingSupervisor, escalateToAdmin, pendingAdmin
            ->count();
        
        $resolvedIncidents = (clone $incidentQuery)->where('statusFlag', 1)->count();
        $resolutionRate = $totalIncidents > 0 ? round(($resolvedIncidents / $totalIncidents) * 100, 1) : 0;

        // Sites
        $siteQuery = DB::table('site_details');
        if (!empty($siteIds)) {
            $siteQuery->whereIn('id', $siteIds);
        }
        $totalSites = $siteQuery->count();

        return [
            'activeGuards' => $activeGuards,
            'totalPatrols' => $totalPatrols,
            'completedPatrols' => $completedPatrols,
            'ongoingPatrols' => $ongoingPatrols,
            'totalDistance' => $totalDistance,
            'avgDistancePerGuard' => $avgDistancePerGuard,
            'attendanceRate' => $attendanceRate,
            'presentCount' => $presentCount,
            'totalIncidents' => $totalIncidents,
            'pendingIncidents' => $pendingIncidents,
            'resolvedIncidents' => $resolvedIncidents,
            'resolutionRate' => $resolutionRate,
            'totalSites' => $totalSites,
        ];
    }

    private function getGuardPerformanceRankings(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        // Get guard performance data
        $patrolQuery = DB::table('patrol_sessions')
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->whereNotNull('patrol_sessions.ended_at')
            ->where('users.isActive', 1);

        if (!empty($siteIds)) {
            $patrolQuery->whereIn('patrol_sessions.site_id', $siteIds);
        }

        $guardPatrols = (clone $patrolQuery)
            ->selectRaw('
                users.id,
                users.name,
                COUNT(*) as patrol_sessions,
                ROUND(SUM(patrol_sessions.distance) / 1000, 2) as total_distance_km
            ')
            ->groupBy('users.id', 'users.name')
            ->get();

        // Get attendance data
        $attendanceQuery = DB::table('attendance')
            ->join('users', 'attendance.user_id', '=', 'users.id')
            ->whereBetween('attendance.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('attendance.attendance_flag', 1)
            ->where('users.isActive', 1);

        if (!empty($siteIds)) {
            $attendanceQuery->whereIn('attendance.site_id', $siteIds);
        }

        $guardAttendance = (clone $attendanceQuery)
            ->selectRaw('
                users.id,
                users.name,
                COUNT(DISTINCT attendance.dateFormat) as days_present
            ')
            ->groupBy('users.id', 'users.name')
            ->get()
            ->keyBy('id');

        // Get incident reports
        $incidentQuery = DB::table('incidence_details')
            ->join('users', 'incidence_details.guard_id', '=', 'users.id')
            ->whereBetween('incidence_details.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('users.isActive', 1);

        if (!empty($siteIds)) {
            $incidentQuery->whereIn('incidence_details.site_id', $siteIds);
        }

        $guardIncidents = (clone $incidentQuery)
            ->selectRaw('
                users.id,
                users.name,
                COUNT(*) as incidents_reported
            ')
            ->groupBy('users.id', 'users.name')
            ->get()
            ->keyBy('id');

        // Calculate performance scores and merge data
        $allGuards = collect();
        foreach ($guardPatrols as $guard) {
            $attendance = $guardAttendance->get($guard->id);
            $incidents = $guardIncidents->get($guard->id);
            
            $daysPresent = $attendance ? $attendance->days_present : 0;
            $incidentsReported = $incidents ? $incidents->incidents_reported : 0;
            
            // Performance score: (distance * 0.4) + (days_present * 10 * 0.3) + (incidents * 20 * 0.3)
            $performanceScore = ($guard->total_distance_km * 0.4) + 
                               ($daysPresent * 10 * 0.3) + 
                               ($incidentsReported * 20 * 0.3);

            $allGuards->push((object)[
                'id' => $guard->id,
                'name' => FormatHelper::formatName($guard->name),
                'patrol_sessions' => $guard->patrol_sessions,
                'total_distance_km' => $guard->total_distance_km,
                'days_present' => $daysPresent,
                'incidents_reported' => $incidentsReported,
                'performance_score' => round($performanceScore, 1),
            ]);
        }

        // Get full performance table with all guards (paginated)
        $allActiveGuards = DB::table('users')
            ->where('isActive', 1)
            ->select('id', 'name')
            ->get();

        $fullPerformance = collect();
        foreach ($allActiveGuards as $guard) {
            $patrol = $guardPatrols->firstWhere('id', $guard->id);
            $attendance = $guardAttendance->get($guard->id);
            $incidents = $guardIncidents->get($guard->id);

            $score = ($patrol ? $patrol->total_distance_km : 0) * 0.4 + 
                     ($attendance ? $attendance->days_present : 0) * 10 * 0.3 + 
                     ($incidents ? $incidents->incidents_reported : 0) * 20 * 0.3;

            $fullPerformance->push((object)[
                'id' => $guard->id,
                'name' => FormatHelper::formatName($guard->name),
                'patrol_sessions' => $patrol ? $patrol->patrol_sessions : 0,
                'total_distance_km' => $patrol ? $patrol->total_distance_km : 0,
                'days_present' => $attendance ? $attendance->days_present : 0,
                'incidents_reported' => $incidents ? $incidents->incidents_reported : 0,
                'performance_score' => round($score, 1),
            ]);
        }

        // Sort and paginate
        $sortedPerformance = $fullPerformance->sortByDesc('performance_score')->values();
        $currentPage = request()->get('page', 1);
        $perPage = 20;
        $total = $sortedPerformance->count();
        $items = $sortedPerformance->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        // Create paginator manually
        $fullPerformancePaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'topPerformers' => $allGuards->sortByDesc('performance_score')->take(5)->values(),
            'fullPerformance' => $fullPerformancePaginated,
        ];
    }

    private function getIncidentStatusTracking(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        $query = DB::table('incidence_details')
            ->whereBetween('dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if (!empty($siteIds)) {
            $query->whereIn('site_id', $siteIds);
        }

        // Status distribution
        $statusDistribution = (clone $query)
            ->selectRaw('statusFlag, COUNT(*) as count')
            ->groupBy('statusFlag')
            ->get()
            ->pluck('count', 'statusFlag');

        // Priority distribution
        $priorityDistribution = (clone $query)
            ->selectRaw('priorityFlag, COUNT(*) as count')
            ->groupBy('priorityFlag')
            ->get()
            ->pluck('count', 'priorityFlag');

        // Incident types
        $incidentTypes = (clone $query)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        // Critical incidents (High/Medium priority, pending)
        $criticalIncidents = (clone $query)
            ->whereIn('priorityFlag', [0, 1]) // High or Medium
            ->whereIn('statusFlag', [0, 3, 4]) // Pending
            ->orderByDesc('dateFormat')
            ->orderByDesc('time')
            ->select('id', 'type', 'site_name', 'guard_name', 'dateFormat', 'priority', 'statusFlag')
            ->get();

        // Resolution time analysis
        $resolutionTime = (clone $query)
            ->where('statusFlag', 1) // Resolved
            ->whereNotNull('adminActionDateTime')
            ->selectRaw('
                type,
                AVG(DATEDIFF(adminActionDateTime, dateFormat)) as avg_days,
                MAX(DATEDIFF(adminActionDateTime, dateFormat)) as max_days
            ')
            ->groupBy('type')
            ->get();

        // Incidents by site
        $incidentsBySite = (clone $query)
            ->selectRaw('
                site_name,
                COUNT(*) as incident_count,
                SUM(CASE WHEN statusFlag = 1 THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN statusFlag IN (0,3,4) THEN 1 ELSE 0 END) as pending_count
            ')
            ->groupBy('site_name')
            ->orderByDesc('incident_count')
            ->get()
            ->map(function($site) {
                $site->resolution_percentage = $site->incident_count > 0 
                    ? round(($site->resolved_count / $site->incident_count) * 100, 1) 
                    : 0;
                return $site;
            });

        return [
            'statusDistribution' => $statusDistribution,
            'priorityDistribution' => $priorityDistribution,
            'incidentTypes' => $incidentTypes,
            'criticalIncidents' => $criticalIncidents,
            'resolutionTime' => $resolutionTime,
            'incidentsBySite' => $incidentsBySite,
        ];
    }

    private function getPatrolAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        $query = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            $query->whereIn('site_id', $siteIds);
        }

        // Patrol by type (with distance)
        $patrolByType = (clone $query)
            ->whereNotNull('ended_at')
            ->selectRaw('
                type, 
                COUNT(*) as count,
                ROUND(SUM(distance) / 1000, 2) as total_distance_km
            ')
            ->groupBy('type')
            ->get();

        // Patrol by session
        $patrolBySession = (clone $query)
            ->selectRaw('session, COUNT(*) as count')
            ->groupBy('session')
            ->get();

        // Foot vs Night patrols
        $footPatrols = (clone $query)->where('session', 'Foot')->count();
        $nightPatrols = (clone $query)
            ->where(function($q) {
                $q->whereTime('started_at', '>=', '18:00:00')
                  ->orWhereTime('started_at', '<=', '06:00:00');
            })
            ->count();

        // Daily patrol trend
        $dailyTrend = (clone $query)
            ->whereNotNull('ended_at')
            ->selectRaw('
                DATE(started_at) as date,
                COUNT(*) as patrol_count,
                ROUND(SUM(distance) / 1000, 2) as distance_km
            ')
            ->groupBy(DB::raw('DATE(started_at)'))
            ->orderBy('date')
            ->get();

        // Distance by site
        $distanceBySite = (clone $query)
            ->join('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->whereNotNull('patrol_sessions.ended_at')
            ->selectRaw('
                site_details.name as site_name,
                ROUND(SUM(patrol_sessions.distance) / 1000, 2) as distance_km
            ')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('distance_km')
            ->limit(10)
            ->get();

        return [
            'patrolByType' => $patrolByType,
            'patrolBySession' => $patrolBySession,
            'footPatrols' => $footPatrols,
            'nightPatrols' => $nightPatrols,
            'dailyTrend' => $dailyTrend,
            'distanceBySite' => $distanceBySite,
        ];
    }

    private function getAttendanceAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        $query = DB::table('attendance')
            ->whereBetween('dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if (!empty($siteIds)) {
            $query->whereIn('site_id', $siteIds);
        }

        // Daily attendance trend
        $dailyTrend = (clone $query)
            ->selectRaw('
                dateFormat as date,
                SUM(CASE WHEN attendance_flag = 1 THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN attendance_flag = 0 THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN lateTime IS NOT NULL AND lateTime > 0 THEN 1 ELSE 0 END) as late
            ')
            ->groupBy('dateFormat')
            ->orderBy('dateFormat')
            ->get();

        // Late attendance analysis
        $lateAttendance = (clone $query)
            ->join('users', 'attendance.user_id', '=', 'users.id')
            ->whereNotNull('lateTime')
            ->where('lateTime', '>', 0)
            ->where('attendance_flag', 1)
            ->selectRaw('
                users.name,
                COUNT(*) as late_count,
                AVG(CAST(lateTime AS UNSIGNED)) as avg_late_minutes
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('late_count')
            ->limit(10)
            ->get();

        // Attendance by site
        $attendanceBySite = (clone $query)
            ->join('site_details', 'attendance.site_id', '=', 'site_details.id')
            ->selectRaw('
                site_details.name as site_name,
                SUM(CASE WHEN attendance_flag = 1 THEN 1 ELSE 0 END) as present_count,
                COUNT(*) as total_count
            ')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('present_count')
            ->get();

        return [
            'dailyTrend' => $dailyTrend,
            'lateAttendance' => $lateAttendance,
            'attendanceBySite' => $attendanceBySite,
        ];
    }

    private function getTimeBasedPatterns(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        $query = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            $query->whereIn('site_id', $siteIds);
        }

        // Hourly distribution
        $hourlyDistribution = (clone $query)
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(started_at)'))
            ->orderBy('hour')
            ->get();

        // Peak patrol hours
        $peakHours = (clone $query)
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(started_at)'))
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Day of week analysis
        $dayOfWeek = (clone $query)
            ->selectRaw('DAYNAME(started_at) as day_name, DAYOFWEEK(started_at) as day_num, COUNT(*) as count')
            ->groupBy('day_name', 'day_num')
            ->orderBy('day_num')
            ->get();

        return [
            'hourlyDistribution' => $hourlyDistribution,
            'peakHours' => $peakHours,
            'dayOfWeek' => $dayOfWeek,
        ];
    }

    private function getRiskZoneAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        // High incident zones
        $incidentQuery = DB::table('incidence_details')
            ->whereBetween('dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('type', ['animal_mortality', 'human_impact']);

        if (!empty($siteIds)) {
            $incidentQuery->whereIn('site_id', $siteIds);
        }

        $highIncidentZones = (clone $incidentQuery)
            ->selectRaw('
                site_name,
                COUNT(*) as incident_count,
                SUM(CASE WHEN type = "animal_mortality" THEN 1 ELSE 0 END) as mortality_count,
                SUM(CASE WHEN type = "human_impact" THEN 1 ELSE 0 END) as human_impact_count
            ')
            ->groupBy('site_name')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc('incident_count')
            ->get();

        // Coverage gaps (sites with no patrols)
        $allSites = DB::table('site_details')
            ->select('id', 'name');
        
        if (!empty($siteIds)) {
            $allSites->whereIn('id', $siteIds);
        }
        $allSites = $allSites->get();

        $patrolledSites = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('site_id')
            ->distinct()
            ->pluck('site_id')
            ->toArray();

        if (!empty($siteIds)) {
            $patrolledSites = array_intersect($patrolledSites, $siteIds);
        }

        $coverageGaps = $allSites->whereNotIn('id', $patrolledSites)->values();

        // Most patrolled sites
        $mostPatrolled = DB::table('patrol_sessions')
            ->join('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            $mostPatrolled->whereIn('patrol_sessions.site_id', $siteIds);
        }

        $mostPatrolled = (clone $mostPatrolled)
            ->selectRaw('site_details.name as site_name, COUNT(*) as patrol_count')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('patrol_count')
            ->limit(10)
            ->get();

        return [
            'highIncidentZones' => $highIncidentZones,
            'coverageGaps' => $coverageGaps,
            'mostPatrolled' => $mostPatrolled,
        ];
    }

    private function getCoverageAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        $allSitesQuery = DB::table('site_details');
        if (!empty($siteIds)) {
            $allSitesQuery->whereIn('id', $siteIds);
        }
        $totalSites = $allSitesQuery->count();

        $patrolledSitesQuery = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('site_id')
            ->distinct()
            ->select('site_id');

        if (!empty($siteIds)) {
            $patrolledSitesQuery->whereIn('site_id', $siteIds);
        }

        $sitesWithPatrols = $patrolledSitesQuery->count();
        $coveragePercentage = $totalSites > 0 ? round(($sitesWithPatrols / $totalSites) * 100, 1) : 0;

        // Sites most patrolled
        $sitesMostPatrolled = DB::table('patrol_sessions')
            ->join('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            $sitesMostPatrolled->whereIn('patrol_sessions.site_id', $siteIds);
        }

        $sitesMostPatrolled = (clone $sitesMostPatrolled)
            ->selectRaw('site_details.name as site_name, COUNT(*) as patrol_count')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('patrol_count')
            ->limit(10)
            ->get();

        // Sites least patrolled
        $sitesLeastPatrolled = DB::table('site_details')
            ->leftJoin('patrol_sessions', function($join) use ($startDate, $endDate) {
                $join->on('site_details.id', '=', 'patrol_sessions.site_id')
                     ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate]);
            })
            ->selectRaw('
                site_details.name as site_name,
                COUNT(patrol_sessions.id) as patrol_count
            ')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderBy('patrol_count')
            ->limit(10);

        if (!empty($siteIds)) {
            $sitesLeastPatrolled->whereIn('site_details.id', $siteIds);
        }

        $sitesLeastPatrolled = $sitesLeastPatrolled->get();

        return [
            'totalSites' => $totalSites,
            'sitesWithPatrols' => $sitesWithPatrols,
            'coveragePercentage' => $coveragePercentage,
            'sitesMostPatrolled' => $sitesMostPatrolled,
            'sitesLeastPatrolled' => $sitesLeastPatrolled,
        ];
    }

    private function getEfficiencyMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $siteIds = $this->resolveSiteIds();

        $query = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            $query->whereIn('site_id', $siteIds);
        }

        // Average patrol duration
        $avgDuration = (clone $query)
            ->whereNotNull('ended_at')
            ->selectRaw('
                AVG(TIMESTAMPDIFF(HOUR, started_at, ended_at)) as avg_hours
            ')
            ->first();

        // Average speed
        $avgSpeed = (clone $query)
            ->whereNotNull('ended_at')
            ->whereNotNull('distance')
            ->where('distance', '>', 0)
            ->selectRaw('
                AVG((distance / 1000) / NULLIF(TIMESTAMPDIFF(HOUR, started_at, ended_at), 0)) as avg_km_per_hour
            ')
            ->first();

        // Completion rate
        $totalPatrols = (clone $query)->count();
        $completedPatrols = (clone $query)->whereNotNull('ended_at')->count();
        $completionRate = $totalPatrols > 0 ? round(($completedPatrols / $totalPatrols) * 100, 1) : 0;

        // Guard efficiency table
        $guardEfficiency = (clone $query)
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->whereNotNull('patrol_sessions.ended_at')
            ->where('users.isActive', 1)
            ->selectRaw('
                users.id as user_id,
                users.name,
                COUNT(*) as session_count,
                ROUND(SUM(patrol_sessions.distance) / 1000, 2) as total_distance_km,
                ROUND(AVG(patrol_sessions.distance) / 1000, 2) as avg_distance_per_session,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, patrol_sessions.started_at, patrol_sessions.ended_at)), 2) as avg_duration_hours
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_distance_km')
            ->get()
            ->map(function($item) {
                $item->name = FormatHelper::formatName($item->name);
                return $item;
            });

        return [
            'avgDurationHours' => $avgDuration ? round($avgDuration->avg_hours, 2) : 0,
            'avgSpeedKmPerHour' => $avgSpeed ? round($avgSpeed->avg_km_per_hour, 2) : 0,
            'completionRate' => $completionRate,
            'guardEfficiency' => $guardEfficiency,
        ];
    }
}
