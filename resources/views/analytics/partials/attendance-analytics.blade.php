{{-- Attendance Analytics --}}
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">✓ Attendance Analytics</h5>
            </div>
            <div class="card-body">
                <canvas id="attendanceTrendChart"></canvas>
                @if(count($attendanceAnalytics['lateAttendance']) > 0)
                <div class="mt-3">
                    <h6>Late Attendance Analysis</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Guard Name</th>
                                    <th>Late Count</th>
                                    <th>Avg Late (min)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendanceAnalytics['lateAttendance'] as $late)
                                <tr>
                                    <td>{{ $late->name }}</td>
                                    <td><span class="badge bg-warning">{{ $late->late_count }}</span></td>
                                    <td>{{ number_format($late->avg_late_minutes, 1) }} min</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">Time Patterns</h6>
            </div>
            <div class="card-body">
                <h6>Peak Patrol Hours</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-3">
                        <thead>
                            <tr>
                                <th>Hour</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timePatterns['peakHours'] as $peak)
                            <tr>
                                <td>{{ $peak->hour }}:00</td>
                                <td>{{ $peak->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <canvas id="hourlyDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

@php
    // Format hourly labels
    $hourlyLabels = $timePatterns['hourlyDistribution']->map(function($item) {
        return $item->hour . ':00';
    })->toArray();
    
    $hourlyData = $timePatterns['hourlyDistribution']->pluck('count')->toArray();
@endphp

<script>
window.attendanceData = {
    dailyLabels: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('date')->toArray()) !!},
    presentData: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('present')->toArray()) !!},
    absentData: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('absent')->toArray()) !!},
    lateData: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('late')->toArray()) !!},
    hourlyLabels: {!! json_encode($hourlyLabels) !!},
    hourlyData: {!! json_encode($hourlyData) !!}
};
</script>

