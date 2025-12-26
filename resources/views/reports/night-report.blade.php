@extends('layouts.app')

@section('content')

<div class="mb-3">
    <h5 class="fw-bold mb-0">Night Patrolling Report</h5>
    <small class="text-muted">After-hours patrol effectiveness</small>
</div>

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
    <div class="card-header fw-semibold">Top Guards (Night)</div>
    <table class="table table-striped mb-0">
        <thead class="table-light">
            <tr>
                <th>Guard</th>
                <th>Total Distance (km)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topGuards as $g)
            <tr>
                <td>{{ $g->guard }}</td>
                <td>{{ $g->distance }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
