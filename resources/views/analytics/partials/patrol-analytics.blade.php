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
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Foot Patrols</h6>
                                <h4 class="text-success mb-0 fw-bold">{{ $patrolAnalytics['footPatrols'] }}</h4>
                            </div>
                            <div class="text-success opacity-50" style="font-size: 1.5rem;">
                                <i class="bi bi-cursor-fill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Night Patrols</h6>
                                <h4 class="text-info mb-0 fw-bold">{{ $patrolAnalytics['nightPatrols'] }}</h4>
                            </div>
                            <div class="text-info opacity-50" style="font-size: 1.5rem;">
                                <i class="bi bi-moon-stars-fill"></i>
                            </div>
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

