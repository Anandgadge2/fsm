@if($incidents->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
        <h5 class="text-muted">No incidents found</h5>
        <p class="text-muted small">No incidents found within {{ $radius }}km of this location</p>
        <small class="text-muted">Try clicking on a different area or adjusting the search radius</small>
    </div>
@else
    <div class="mb-3 p-3 bg-light rounded">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">
                    <i class="bi bi-geo-alt-fill text-primary"></i> 
                    Found <strong>{{ $incidents->count() }}</strong> incident(s) nearby
                </h6>
                <small class="text-muted">Within {{ $radius }}km radius</small>
            </div>
            <span class="badge bg-primary">Latest Results</span>
        </div>
    </div>

    <div class="row g-3">
        @foreach($incidents as $incident)
            <div class="col-12">
                <div class="card incident-card border-start border-4 
                    @if($incident->type === 'animal_mortality') border-danger
                    @elseif($incident->type === 'human_impact') border-warning
                    @elseif($incident->type === 'animal_sighting') border-primary
                    @else border-success
                    @endif
                    h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <span class="badge 
                                    @if($incident->type === 'animal_mortality') bg-danger
                                    @elseif($incident->type === 'human_impact') bg-warning
                                    @elseif($incident->type === 'animal_sighting') bg-primary
                                    @else bg-success
                                    @endif
                                    mb-2">
                                    {{ ucwords(str_replace('_', ' ', $incident->type)) }}
                                </span>
                                <h6 class="mb-0 fw-bold">{{ $incident->compartment ?? 'Unknown Location' }}</h6>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo"></i> {{ number_format($incident->distance, 2) }} km
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($incident->created_at)->diffForHumans() }}
                                </small>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Guard</small>
                                <span class="fw-semibold">
                                    <i class="bi bi-person-badge"></i> {{ $incident->guard ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Date & Time</small>
                                <span class="fw-semibold">
                                    <i class="bi bi-calendar-event"></i> 
                                    {{ \Carbon\Carbon::parse($incident->created_at)->format('d M Y, h:i A') }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Range</small>
                                <span class="fw-semibold">
                                    <i class="bi bi-signpost-2"></i> 
                                    {{ $incident->range_name ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Beat ID</small>
                                <span class="fw-semibold">
                                    <i class="bi bi-hash"></i> 
                                    {{ $incident->beat_id ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Patrol Type</small>
                                <span class="fw-semibold">
                                    <i class="bi bi-arrow-repeat"></i> 
                                    {{ ucfirst($incident->patrol_type ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Session</small>
                                <span class="fw-semibold">
                                    <i class="bi bi-bicycle"></i> 
                                    {{ ucfirst($incident->session ?? 'N/A') }}
                                </span>
                            </div>
                        </div>

                        @php
                            $payload = json_decode($incident->payload, true);
                        @endphp

                        @if(is_array($payload) && count($payload) > 0)
                            <div class="mb-3 p-2 bg-light rounded">
                                <small class="text-muted fw-bold d-block mb-2">Incident Details:</small>
                                <div class="row g-2">
                                    @foreach($payload as $key => $value)
                                        @if(!empty($value) && !is_array($value))
                                            <div class="col-6">
                                                <small class="text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}:</small>
                                                <strong class="d-block">{{ $value }}</strong>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($incident->notes)
                            <div class="alert alert-info alert-sm mb-3 py-2">
                                <small class="text-muted fw-bold">Notes:</small>
                                <p class="mb-0 small">{{ $incident->notes }}</p>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div>
                                <small class="text-muted">
                                    Compartment: <strong>{{ $incident->compartment ?? 'Unknown' }}</strong>
                                </small>
                            </div>
                            <div>
                                <span class="badge 
                                    @if($incident->severity == 5) severity-high
                                    @elseif($incident->severity >= 3) bg-warning
                                    @else severity-low
                                    @endif
                                    text-white">
                                    Severity: {{ $incident->severity }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
.incident-card {
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.incident-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.alert-sm {
    font-size: 0.875rem;
}

.severity-high {
    background: #c62828;
}

.severity-low {
    background: #388e3c;
}
</style>