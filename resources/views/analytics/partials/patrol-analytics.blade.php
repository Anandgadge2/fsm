{{-- Patrol Analytics --}}
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🚶 Patrol Analytics</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-success mb-0">{{ $patrolAnalytics['footPatrols'] }}</h4>
                            <small class="text-muted">Foot Patrols</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-info mb-0">{{ $patrolAnalytics['nightPatrols'] }}</h4>
                            <small class="text-muted">Night Patrols</small>
                        </div>
                    </div>
                </div>
                <canvas id="patrolTypeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📈 Daily Patrol Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyPatrolTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
window.patrolAnalyticsData = {
    typeLabels: {!! json_encode($patrolAnalytics['patrolByType']->pluck('type')->toArray()) !!},
    typeCounts: {!! json_encode($patrolAnalytics['patrolByType']->pluck('count')->toArray()) !!},
    typeDistances: {!! json_encode($patrolAnalytics['patrolByType']->pluck('total_distance_km')->toArray()) !!},
    dailyLabels: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('date')->toArray()) !!},
    dailyCounts: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('patrol_count')->toArray()) !!},
    dailyDistances: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('distance_km')->toArray()) !!}
};
</script>

