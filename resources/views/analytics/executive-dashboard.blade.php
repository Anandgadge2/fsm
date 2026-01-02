@extends('layouts.app')

@section('content')

<div class="container-fluid">
   
    {{-- Global Filter Include if needed, or it might be in layout --}}
    {{-- @include('partials.global-filters') --}}

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="bg-white p-2 shadow-sm border rounded">
            <h2 class="fw-bold mb-1">Executive Analytics Dashboard</h2>
            <p class="text-muted mb-0">
                Comprehensive forest guard analytics including Patrol Efficiency, Attendance Reliability, and Incident Management.
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-white shadow-sm text-dark p-2 border">
                <i class="bi bi-calendar-range me-1"></i>
                Period: <strong>{{ $startDate->format('d M Y') }}</strong> - <strong>{{ $endDate->format('d M Y') }}</strong>
            </span>
        </div>
    </div>

    {{-- Dashboard Context / Help --}}
    <div class="alert alert-light border shadow-sm mb-4">
        <div class="d-flex gap-3 align-items-start">
            <div class="text-primary fs-4"><i class="bi bi-info-circle"></i></div>
            <div>
                <h6 class="fw-bold mb-1">Dashboard Insights Guide</h6>
                <p class="mb-0 small text-muted">
                    • <strong>KPI Cards:</strong> Top-level metrics comparing current period performance.<br>
                    • <strong>Guard Performance:</strong> Scoring based on Patrol Distance (40%), Attendance (30%), and Incidents Reported (30%).<br>
                    • <strong>Incident Tracking:</strong> Heatmap of incident types and status distribution.<br>
                    • <strong>Attendance Analytics:</strong> Daily presence trends and late arrival tracking.
                </p>
            </div>
        </div>
    </div>

    {{-- Key Performance Indicators --}}
    @include('analytics.partials.kpi-cards', ['kpis' => $kpis, 'coverageAnalysis' => $coverageAnalysis])

    {{-- Guard Performance Rankings --}}
    <div class="mb-2">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Performance Metrics</small>
    </div>
    @include('analytics.partials.guard-performance', ['guardPerformance' => $guardPerformance])

    {{-- Incident Tracking --}}
    <div class="mt-4 mb-2">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Safety & Incidents</small>
    </div>
    @include('analytics.partials.incident-tracking', ['incidentTracking' => $incidentTracking])

    {{-- Patrol Analytics --}}
    <div class="mt-4 mb-2">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Patrol Operations</small>
    </div>
    @include('analytics.partials.patrol-analytics', ['patrolAnalytics' => $patrolAnalytics])

    {{-- Attendance Analytics --}}
    <div class="mt-4 mb-2">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Workforce Availability</small>
    </div>
    @include('analytics.partials.attendance-analytics', [
        'attendanceAnalytics' => $attendanceAnalytics,
        'timePatterns' => $timePatterns
    ])

    <!-- {{-- Risk Zones & Coverage (Optional) --}}
    @include('analytics.partials.risk-coverage', ['riskZones' => $riskZones, 'coverageAnalysis' => $coverageAnalysis]) -->

    {{-- Efficiency Metrics --}}
    <div class="mt-4 mb-2">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Operational Efficiency</small>
    </div>
    @include('analytics.partials.efficiency-metrics', ['efficiencyMetrics' => $efficiencyMetrics])
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('js/executive-dashboard-charts.js') }}"></script>
@endpush
