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
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Avg Duration</h6>
                                <h4 class="mb-0 fw-bold text-primary">{{ number_format($efficiencyMetrics['avgDurationHours'], 2) }} hrs</h4>
                            </div>
                            <div class="text-primary opacity-50" style="font-size: 1.5rem;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Avg Speed</h6>
                                <h4 class="mb-0 fw-bold text-info">{{ number_format($efficiencyMetrics['avgSpeedKmPerHour'], 2) }} km/h</h4>
                            </div>
                            <div class="text-info opacity-50" style="font-size: 1.5rem;">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Completion</h6>
                                <h4 class="mb-0 fw-bold text-success">{{ number_format($efficiencyMetrics['completionRate'], 1) }}%</h4>
                            </div>
                            <div class="text-success opacity-50" style="font-size: 1.5rem;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white h-100 d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <h6 class="text-muted text-uppercase small mb-1">Active Guards</h6>
                                <h4 class="mb-0 fw-bold text-dark">{{ count($efficiencyMetrics['guardEfficiency']) }}</h4>
                            </div>
                            <div class="text-dark opacity-50" style="font-size: 1.5rem;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover sortable-table sticky-header">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th data-sortable style="min-width: 150px;">Guard Name</th>
                                <th data-sortable data-type="number" class="text-center" style="min-width: 80px;">Sessions</th>
                                <th data-sortable data-type="number" class="text-center" style="min-width: 120px;">Total Distance</th>
                                <th data-sortable data-type="number" class="text-center" style="min-width: 140px;">Avg Distance/Session</th>
                                <th data-sortable data-type="number" class="text-center" style="min-width: 110px;">Avg Duration</th>
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
                                <td class="text-center">{{ $eff->session_count }}</td>
                                <td class="text-center">{{ number_format($eff->total_distance_km, 2) }} km</td>
                                <td class="text-center">{{ number_format($eff->avg_distance_per_session, 2) }} km</td>
                                <td class="text-center">{{ number_format($eff->avg_duration_hours, 2) }} hrs</td>
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
    transition: background-color 0.2s ease;
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

