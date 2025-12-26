@extends('layouts.app')

@section('content')

    <h5 class="fw-bold mb-3">Foot Patrolling Report</h5>

<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Sessions</small>
            <h4 class="fw-bold">{{ $totalSessions }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Completed</small>
            <h4 class="fw-bold text-success">{{ $completed }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Ongoing</small>
            <h4 class="fw-bold text-warning">{{ $ongoing }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Distance (km)</small>
            <h4 class="fw-bold">{{ $totalDistance }}</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Guard Performance</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Guard</th>
                    <th>Sessions</th>
                    <th>Completed</th>
                    <th>Ongoing</th>
                    <th>Total Distance (km)</th>
                    <th>Avg Speed (km/h)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guardStats as $g)
                <tr>
                    <td>{{ $g->guard }}</td>
                    <td>{{ $g->total_sessions }}</td>
                    <td>{{ $g->completed }}</td>
                    <td>{{ $g->ongoing }}</td>
                    <td>{{ $g->total_distance }}</td>
                    <td>{{ $g->km_per_hour }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-2 d-flex justify-content-end">
        {{ $guardStats->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection
