{{-- Risk Zones & Coverage --}}
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">⚠️ Risk Zones</h5>
            </div>
            <div class="card-body">
                @if(count($riskZones['highIncidentZones']) > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Site Name</th>
                                <th>Type</th>
                                <th>Incidents</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riskZones['highIncidentZones']->take(10) as $zone)
                            <tr>
                                <td>{{ $zone->site_name }}</td>
                                <td>
                                    @if($zone->mortality_count > 0)
                                        <span class="badge bg-danger">Mortality: {{ $zone->mortality_count }}</span>
                                    @endif
                                    @if($zone->human_impact_count > 0)
                                        <span class="badge bg-warning">Human Impact: {{ $zone->human_impact_count }}</span>
                                    @endif
                                </td>
                                <td>{{ $zone->incident_count }}</td>
                                <td>-</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No high-risk zones identified in this period.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">📍 Coverage Gaps</h5>
            </div>
            <div class="card-body">
                @if(count($riskZones['coverageGaps']) > 0)
                <div class="alert alert-warning">
                    <strong>Sites with No Patrols:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($riskZones['coverageGaps']->take(10) as $gap)
                        <li>{{ $gap->name }}</li>
                        @endforeach
                    </ul>
                </div>
                @else
                <p class="text-success">✅ All sites have patrol coverage in this period.</p>
                @endif

                <div class="mt-3">
                    <h6>Most Patrolled Sites</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Patrols</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riskZones['mostPatrolled'] as $site)
                                <tr>
                                    <td>{{ $site->site_name }}</td>
                                    <td>{{ $site->patrol_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

