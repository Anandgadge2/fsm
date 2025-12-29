<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\FormatHelper;

class GuardDetailController extends Controller
{
    public function getGuardDetails($guardId)
    {
        try {
            $guard = DB::table('users')
                ->where('id', $guardId)
                ->where('isActive', 1)
                ->first();

            if (!$guard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guard not found'
                ], 404);
            }

            // Format guard name
            $guard->name = FormatHelper::formatName($guard->name);

            // Get attendance stats
            $attendanceStats = $this->getAttendanceStats($guardId);
            
            // Get patrol stats
            $patrolStats = $this->getPatrolStats($guardId);
            
            // Get incident stats
            $incidentStats = $this->getIncidentStats($guardId);
            
            // Get patrol paths with GeoJSON
            $patrolPaths = $this->getPatrolPaths($guardId);

            return response()->json([
                'success' => true,
                'guard' => [
                    'id' => $guard->id,
                    'name' => $guard->name,
                    'gen_id' => $guard->gen_id,
                    'contact' => $guard->contact,
                    'email' => $guard->email,
                    'designation' => FormatHelper::formatName($guard->designation ?? ''),
                    'company_name' => $guard->company_name,
                    'attendance_stats' => $attendanceStats,
                    'patrol_stats' => $patrolStats,
                    'incident_stats' => $incidentStats,
                    'patrol_paths' => $patrolPaths,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching guard details: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getAttendanceStats($guardId)
    {
        $totalDays = DB::table('attendance')
            ->where('user_id', $guardId)
            ->distinct('dateFormat')
            ->count('dateFormat');

        $presentCount = DB::table('attendance')
            ->where('user_id', $guardId)
            ->where('attendance_flag', 1)
            ->count();

        $absentCount = DB::table('attendance')
            ->where('user_id', $guardId)
            ->where('attendance_flag', 0)
            ->count();

        $lateCount = DB::table('attendance')
            ->where('user_id', $guardId)
            ->whereNotNull('lateTime')
            ->where('lateTime', '>', 0)
            ->where('attendance_flag', 1)
            ->count();

        $attendanceRate = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 1) : 0;

        return [
            'total_days' => $totalDays,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'present_days' => $presentCount,
            'attendance_rate' => $attendanceRate,
        ];
    }

    private function getPatrolStats($guardId)
    {
        $totalSessions = DB::table('patrol_sessions')
            ->where('user_id', $guardId)
            ->count();

        $completedSessions = DB::table('patrol_sessions')
            ->where('user_id', $guardId)
            ->whereNotNull('ended_at')
            ->count();

        $ongoingSessions = $totalSessions - $completedSessions;

        $totalDistance = DB::table('patrol_sessions')
            ->where('user_id', $guardId)
            ->whereNotNull('ended_at')
            ->sum('distance') / 1000; // Convert to km

        $avgDistance = $completedSessions > 0 
            ? (DB::table('patrol_sessions')
                ->where('user_id', $guardId)
                ->whereNotNull('ended_at')
                ->avg('distance') / 1000)
            : 0;

        $avgDuration = DB::table('patrol_sessions')
            ->where('user_id', $guardId)
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, ended_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'ongoing_sessions' => $ongoingSessions,
            'total_distance_km' => round($totalDistance, 2),
            'avg_distance_km' => round($avgDistance, 2),
            'avg_duration_hours' => round($avgDuration, 2),
        ];
    }

    private function getIncidentStats($guardId)
    {
        $totalIncidents = DB::table('incidence_details')
            ->where('guard_id', $guardId)
            ->count();

        $incidentsByType = DB::table('incidence_details')
            ->where('guard_id', $guardId)
            ->selectRaw('type, statusFlag, COUNT(*) as count')
            ->groupBy('type', 'statusFlag')
            ->get()
            ->groupBy('type')
            ->map(function($group) {
                $statusMap = [
                    0 => ['name' => 'Pending Supervisor', 'color' => 'warning'],
                    1 => ['name' => 'Resolved', 'color' => 'success'],
                    2 => ['name' => 'Ignored', 'color' => 'secondary'],
                    3 => ['name' => 'Escalated to Admin', 'color' => 'info'],
                    4 => ['name' => 'Pending Admin', 'color' => 'warning'],
                    5 => ['name' => 'Escalated to Client', 'color' => 'danger'],
                    6 => ['name' => 'Reverted', 'color' => 'secondary'],
                ];
                
                return $group->map(function($inc) use ($statusMap) {
                    $status = $statusMap[$inc->statusFlag] ?? ['name' => 'Unknown', 'color' => 'secondary'];
                    return [
                        'type' => $inc->type,
                        'count' => $inc->count,
                        'status' => $status['name'],
                        'status_color' => $status['color'],
                    ];
                });
            })
            ->flatten(1)
            ->values()
            ->toArray();

        return [
            'total_incidents' => $totalIncidents,
            'incidents_by_type' => $incidentsByType,
        ];
    }

    private function getPatrolPaths($guardId)
    {
        return DB::table('patrol_sessions')
            ->where('user_id', $guardId)
            ->whereNotNull('path_geojson')
            ->whereNotNull('ended_at')
            ->select('id', 'path_geojson', 'started_at', 'ended_at', 'distance', 'type', 'session')
            ->orderByDesc('started_at')
            ->limit(10) // Limit to last 10 patrols
            ->get()
            ->map(function($patrol) {
                return [
                    'id' => $patrol->id,
                    'path_geojson' => $patrol->path_geojson,
                    'started_at' => $patrol->started_at,
                    'ended_at' => $patrol->ended_at,
                    'distance' => $patrol->distance,
                    'type' => $patrol->type,
                    'session' => $patrol->session,
                ];
            });
    }
}

