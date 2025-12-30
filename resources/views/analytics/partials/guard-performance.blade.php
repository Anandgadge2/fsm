{{-- Guard Performance Rankings --}}
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">🏆 Top Performing Guards</h5>
            </div>
            <div class="card-body" style="min-height: 260px;">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 sortable-table">
                        <thead>
                            <tr>
                                <th data-sortable>Rank</th>
                                <th data-sortable>Guard Name</th>
                                <th data-sortable data-type="number">Patrols</th>
                                <th data-sortable data-type="number">Distance (km)</th>
                                <th data-sortable data-type="number">Days Present</th>
                                <th data-sortable data-type="number">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardPerformance['topPerformers'] as $index => $guard)
                                <tr>
                                    <td><strong>#{{ $index + 1 }}</strong></td>
                                    <td>
                                        <a href="#" class="guard-name-link" data-guard-id="{{ $guard->id }}">
                                            {{ $guard->name }}
                                        </a>
                                    </td>
                                    <td>{{ $guard->patrol_sessions }}</td>
                                    <td>{{ number_format($guard->total_distance_km, 2) }}</td>
                                    <td>{{ $guard->days_present }}</td>
                                    <td><span class="badge bg-success">{{ number_format($guard->performance_score, 1) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No guard performance data for selected period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">📊 Guard Performance Overview</h5>
            </div>
            <div class="card-body" style="min-height: 260px;">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0 sortable-table">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th data-sortable>#</th>
                                <th data-sortable>Guard</th>
                                <th data-sortable data-type="number">Patrols</th>
                                <th data-sortable data-type="number">Distance</th>
                                <th data-sortable data-type="number">Present</th>
                                <th data-sortable data-type="number">Incidents</th>
                                <th data-sortable data-type="number">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardPerformance['fullPerformance'] as $index => $guard)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="#" class="guard-name-link" data-guard-id="{{ $guard->id }}">
                                            {{ $guard->name }}
                                        </a>
                                    </td>
                                    <td>{{ $guard->patrol_sessions }}</td>
                                    <td>{{ number_format($guard->total_distance_km, 2) }} km</td>
                                    <td>{{ $guard->days_present }} days</td>
                                    <td>{{ $guard->incidents_reported }}</td>
                                    <td><span class="badge bg-primary">{{ number_format($guard->performance_score, 1) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No guards found for selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

