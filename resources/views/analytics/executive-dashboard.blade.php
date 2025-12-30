@extends('layouts.app')

@section('content')

<div class="container-fluid">
   

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Executive Analytics Dashboard</h2>
            <p class="text-muted mb-0">Comprehensive forest guard analytics and insights</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Period: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</small>
        </div>
    </div>

    {{-- Key Performance Indicators --}}
    @include('analytics.partials.kpi-cards', ['kpis' => $kpis, 'coverageAnalysis' => $coverageAnalysis])

    {{-- Guard Performance Rankings --}}
    @include('analytics.partials.guard-performance', ['guardPerformance' => $guardPerformance])

    {{-- Incident Tracking --}}
    @include('analytics.partials.incident-tracking', ['incidentTracking' => $incidentTracking])

    {{-- Patrol Analytics --}}
    @include('analytics.partials.patrol-analytics', ['patrolAnalytics' => $patrolAnalytics])

    {{-- Attendance Analytics --}}
    @include('analytics.partials.attendance-analytics', [
        'attendanceAnalytics' => $attendanceAnalytics,
        'timePatterns' => $timePatterns
    ])

    <!-- {{-- Risk Zones & Coverage --}}
    @include('analytics.partials.risk-coverage', ['riskZones' => $riskZones, 'coverageAnalysis' => $coverageAnalysis]) -->

    {{-- Efficiency Metrics --}}
    @include('analytics.partials.efficiency-metrics', ['efficiencyMetrics' => $efficiencyMetrics])
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('js/executive-dashboard-charts.js') }}"></script>
@endpush

