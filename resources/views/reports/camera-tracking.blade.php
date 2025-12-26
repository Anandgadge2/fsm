@extends('layouts.app')

@section('content')

<div class="mb-3">
    <h5 class="fw-bold mb-0">Camera & Tracking Report</h5>
    <small class="text-muted">Live and historical patrol path monitoring</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Guards</small>
            <h4 class="fw-bold">{{ $stats['total_guards'] }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Active Patrols</small>
            <h4 class="fw-bold text-warning">{{ $stats['active_patrols'] }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Completed Patrols</small>
            <h4 class="fw-bold text-success">{{ $stats['completed_patrols'] }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Distance (km)</small>
            <h4 class="fw-bold">{{ $stats['total_distance_km'] }}</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Active Guards</div>
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Designation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guards as $g)
            <tr>
                <td>{{ $g->name }}</td>
                <td>{{ $g->designation }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
