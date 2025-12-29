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
                <div class="table-responsive">
                    <table class="table table-sm table-hover sortable-table">
                        <thead>
                            <tr>
                                <th data-sortable>Guard Name</th>
                                <th data-sortable data-type="number">Sessions</th>
                                <th data-sortable data-type="number">Total Distance</th>
                                <th data-sortable data-type="number">Avg Distance/Session</th>
                                <th data-sortable data-type="number">Avg Duration</th>
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
                                        <a href="#" class="guard-name-link" data-guard-id="{{ $guardId }}">
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
            </div>
        </div>
    </div>
</div>

