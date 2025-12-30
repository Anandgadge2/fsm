{{-- Efficiency Metrics --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">⚡ Efficiency Metrics</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="mb-0">{{ number_format($efficiencyMetrics['avgDurationHours'], 2) }} hrs</h5>
                            <small class="text-muted">Avg Patrol Duration</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="mb-0">{{ number_format($efficiencyMetrics['avgSpeedKmPerHour'], 2) }} km/h</h5>
                            <small class="text-muted">Avg Speed</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="mb-0">{{ number_format($efficiencyMetrics['completionRate'], 1) }}%</h5>
                            <small class="text-muted">Completion Rate</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="mb-0">{{ count($efficiencyMetrics['guardEfficiency']) }}</h5>
                            <small class="text-muted">Active Guards</small>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover sortable-table sticky-header">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th data-sortable style="min-width: 150px;">Guard Name</th>
                                <th data-sortable data-type="number" style="min-width: 80px;">Sessions</th>
                                <th data-sortable data-type="number" style="min-width: 120px;">Total Distance</th>
                                <th data-sortable data-type="number" style="min-width: 140px;">Avg Distance/Session</th>
                                <th data-sortable data-type="number" style="min-width: 110px;">Avg Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($efficiencyMetrics['guardEfficiency'] as $eff)
                            <tr>
                                <td>
                                    @php
                                        // Extract guard ID from name or use a query
                                        $guardId = $eff->user_id ?? null;
                                    @endphp
                                    @if($guardId)
                                        <a href="#" class="guard-name-link text-decoration-none" data-guard-id="{{ $guardId }}">
                                            {{ \App\Helpers\FormatHelper::formatName($eff->name) }}
                                        </a>
                                    @else
                                        {{ \App\Helpers\FormatHelper::formatName($eff->name) }}
                                    @endif
                                </td>
                                <td>{{ $eff->session_count }}</td>
                                <td>{{ number_format($eff->total_distance_km, 2) }} km</td>
                                <td>{{ number_format($eff->avg_distance_per_session, 2) }} km</td>
                                <td>{{ number_format($eff->avg_duration_hours, 2) }} hrs</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if(count($efficiencyMetrics['guardEfficiency']) > 10)
                <div class="text-center mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Showing {{ count($efficiencyMetrics['guardEfficiency']) }} guards. Scroll to see more.
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Sticky header for table */
.sticky-header .sticky-top {
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    background-color: #f8f9fa !important;
}

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

/* Table row hover effects */
.sticky-header tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Guard name link styling */
.guard-name-link {
    color: #0d6efd !important;
    font-weight: 500;
}

.guard-name-link:hover {
    color: #0a58ca !important;
    text-decoration: underline !important;
}

/* Ensure proper column widths */
.sticky-header th {
    white-space: nowrap;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
}

.sticky-header td {
    white-space: nowrap;
    vertical-align: middle;
}
</style>

