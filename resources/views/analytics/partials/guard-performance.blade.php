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
                                <th data-sortable class="text-center">Rank</th>
                                <th data-sortable>Guard Name</th>
                                <th data-sortable data-type="number" class="text-center">Patrols</th>
                                <th data-sortable data-type="number" class="text-center">Distance (km)</th>
                                <th data-sortable data-type="number" class="text-center">Days Present</th>
                                <th data-sortable data-type="number" class="text-center">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardPerformance['topPerformers'] as $index => $guard)
                                <tr>
                                    <td class="text-center"><strong>#{{ $index + 1 }}</strong></td>
                                    <td>
                                        <a href="#" class="guard-name-link" data-guard-id="{{ $guard->id }}">
                                            {{ $guard->name }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $guard->patrol_sessions }}</td>
                                    <td class="text-center">{{ number_format($guard->total_distance_km, 2) }}</td>
                                    <td class="text-center">{{ $guard->days_present }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ number_format($guard->performance_score, 1) }}</span></td>
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
                                <th data-sortable class="text-center">#</th>
                                <th data-sortable>Guard</th>
                                <th data-sortable data-type="number" class="text-center">Patrols</th>
                                <th data-sortable data-type="number" class="text-center">Distance</th>
                                <th data-sortable data-type="number" class="text-center">Present</th>
                                <th data-sortable data-type="number" class="text-center">Incidents</th>
                                <th data-sortable data-type="number" class="text-center">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardPerformance['fullPerformance'] as $index => $guard)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <a href="#" class="guard-name-link" data-guard-id="{{ $guard->id }}">
                                            {{ $guard->name }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $guard->patrol_sessions }}</td>
                                    <td class="text-center">{{ number_format($guard->total_distance_km, 2) }} km</td>
                                    <td class="text-center">{{ $guard->days_present }} days</td>
                                    <td class="text-center">{{ $guard->incidents_reported }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ number_format($guard->performance_score, 1) }}</span></td>
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

