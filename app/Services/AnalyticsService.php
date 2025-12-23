<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getDashboardData(array $filters): array
    {
        return [
            'kpis' => $this->kpis(),
            'attendance' => $this->attendanceAnalytics($filters),
            'patrols' => $this->patrolAnalytics($filters),
        ];
    }

    private function kpis(): array
    {
        return [
            'guards' => DB::table('users')->where('isActive', 1)->count(),
            'sites' => DB::table('site_details')->count(),
            'patrols' => DB::table('patrol_sessions')->count(),
            'incidents' => DB::table('incidence_details')->count(),
        ];
    }

    private function attendanceAnalytics(array $filters): array
    {
        $query = DB::table('attendance');

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('dateFormat', [$filters['from'], $filters['to']]);
        }

        return [
            'present' => (clone $query)->where('attendance_flag', 1)->count(),
            'absent' => (clone $query)->where('attendance_flag', 0)->count(),
            'trend' => (clone $query)
                ->selectRaw('dateFormat, COUNT(*) as total')
                ->groupBy('dateFormat')
                ->orderBy('dateFormat')
                ->get(),
        ];
    }

    private function patrolAnalytics(array $filters): array
    {
        $query = DB::table('patrol_sessions');

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('started_at', [$filters['from'], $filters['to']]);
        }

        return [
            'distance' => round($query->sum('distance'), 2),
            'avg' => round($query->avg('distance'), 2),
            'byType' => $query
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->get(),
        ];
    }

    public function attendanceByRange($filters)
{
    $q = DB::table('attendance')
        ->join('site_details','attendance.site_id','=','site_details.id');

    $this->applyFilters($q, $filters);

    return $q->selectRaw('site_details.client_name as range, COUNT(*) as total')
        ->groupBy('range')
        ->get();
}

private function applyFilters($query, $filters)
{
    if (!empty($filters['from']) && !empty($filters['to'])) {
        $query->whereBetween('attendance.dateFormat', [$filters['from'], $filters['to']]);
    }
    if (!empty($filters['range'])) {
        $query->where('site_details.client_name', $filters['range']);
    }
    if (!empty($filters['beat'])) {
        $query->where('site_details.name', $filters['beat']);
    }
    if (!empty($filters['geofence'])) {
        $query->where('attendance.geo_name', $filters['geofence']);
    }
}

}
