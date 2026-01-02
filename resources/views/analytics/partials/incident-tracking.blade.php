{{-- Incident Status Tracking --}}
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">🚨 Incident Status Tracking</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Critical Pending</h6>
                                <h3 class="text-danger mb-0 fw-bold">{{ count($incidentTracking['criticalIncidents']) }}</h3>
                            </div>
                            <div class="text-danger" style="font-size: 2rem;">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div style="height:150px">
                            <canvas id="incidentStatusChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div style="height:150px">
                            <canvas id="incidentPriorityChart"></canvas>
                        </div>
                    </div>
                </div>

                @if(count($incidentTracking['criticalIncidents']) > 0)
                <div class="alert alert-warning">
                    <strong>Recent Critical Incidents Requiring Attention:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($incidentTracking['criticalIncidents']->take(5) as $incident)
                        <li>{{ $incident->type }} at {{ $incident->site_name }} - {{ $incident->dateFormat }} ({{ $incident->guard_name }})</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm sortable-table sticky-header">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th data-sortable>Site Name</th>
                                <th data-sortable data-type="number" class="text-center">Total</th>
                                <th data-sortable data-type="number" class="text-center">Resolved</th>
                                <th data-sortable data-type="number" class="text-center">Pending</th>
                                <th data-sortable data-type="number" class="text-center">Resolution %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incidentTracking['incidentsBySite'] as $site)
                                <tr>
                                    <td>{{ $site->site_name }}</td>
                                    <td class="text-center">{{ $site->incident_count }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $site->resolved_count }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $site->pending_count }}</span></td>
                                    <td class="text-center">{{ $site->resolution_percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No incidents for selected period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">Incident Types</h6>
            </div>
            <div class="card-body">
                <div style="height:210px">
                    <canvas id="incidentTypeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">Resolution Time (Days)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Avg Days</th>
                                <th>Max Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incidentTracking['resolutionTime'] as $rt)
                                <tr>
                                    <td>{{ $rt->type }}</td>
                                    <td>{{ number_format($rt->avg_days, 1) }}</td>
                                    <td>{{ $rt->max_days }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No resolution data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    // Map analytics flags to names
    // Executive incident analytics is derived from patrol_logs; we treat these as severity buckets.
    $statusMap = [
        5 => 'Critical',
        4 => 'High',
        3 => 'Medium',
        2 => 'Low',
        1 => 'Info'
    ];
    
    // Map priority flags to names
    $priorityMap = [
        0 => 'High',
        1 => 'Medium',
        2 => 'Low'
    ];
    
    // Prepare status data
    $statusLabels = [];
    $statusData = [];
    foreach ($incidentTracking['statusDistribution'] as $flag => $count) {
        $statusLabels[] = $statusMap[$flag] ?? 'Unknown';
        $statusData[] = $count;
    }
    
    // Prepare priority data
    $priorityLabels = [];
    $priorityData = [];
    foreach ($incidentTracking['priorityDistribution'] as $flag => $count) {
        $priorityLabels[] = $priorityMap[$flag] ?? 'Unknown';
        $priorityData[] = $count;
    }
    
    // Prepare type data
    $typeLabels = $incidentTracking['incidentTypes']->pluck('type')->toArray();
    $typeData = $incidentTracking['incidentTypes']->pluck('count')->toArray();
@endphp

<script>
// Store data for charts
window.incidentTrackingData = {
    statusLabels: {!! json_encode($statusLabels) !!},
    statusData: {!! json_encode($statusData) !!},
    priorityLabels: {!! json_encode($priorityLabels) !!},
    priorityData: {!! json_encode($priorityData) !!},
    typeLabels: {!! json_encode($typeLabels) !!},
    typeData: {!! json_encode($typeData) !!}
};
</script>

<style>
    /* Enhanced scrollbar styling */
.table-responsive::-webkit-scrollbar {
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>