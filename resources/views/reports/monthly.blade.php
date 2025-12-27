@extends('layouts.app')

@section('content')

{{-- ================= HEADER ================= --}}
<div class="mb-3">
    <h5 class="fw-bold mb-0">Monthly Patrol Report</h5>
    <small class="text-muted">Year-wise patrol performance summary</small>
</div>

{{-- ================= FILTER ================= --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="year" class="form-select">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-success">Apply</button>
    </div>
</form>

{{-- ================= KPIs ================= --}}
<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Sessions</small>
            <h4 class="fw-bold">{{ $kpis['total_sessions'] }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Guards</small>
            <h4 class="fw-bold">{{ $kpis['total_guards'] }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Total Distance (km)</small>
            <h4 class="fw-bold">{{ $kpis['total_distance'] }}</h4>
        </div>
    </div>
    <div class="col-md">
        <div class="card p-3 text-center">
            <small>Avg / Session (km)</small>
            <h4 class="fw-bold">{{ $kpis['avg_per_session'] }}</h4>
        </div>
    </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="card">
    <div class="card-header fw-semibold">Month-wise Summary</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0 smart-sort">
            <thead class="table-light">
                <tr>
                    <th>Month</th>
                    <th>Sessions</th>
                    <th>Guards</th>
                    <th>Distance (km)</th>
                    <th>Avg Distance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly as $m)
                <tr>
                    <td>{{ \Carbon\Carbon::create()->month($m->month)->format('F') }}</td>
                    <td>{{ $m->sessions }}</td>
                    <td>{{ $m->guards }}</td>
                    <td>{{ $m->distance }}</td>
                    <td>{{ $m->avg_distance }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
