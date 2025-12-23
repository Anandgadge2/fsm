@extends('layouts.app')

@section('content')

<div class="container">

    {{-- Header --}}
    <div class="text-center my-4">
        <h2 class="fw-bold dashboard-title">Analytics Dashboard</h2>
        <p class="dashboard-subtitle">
            Unified view of patrolling, attendance, and reports
        </p>
    </div>

    {{-- Dashboard Tiles --}}
    <div class="row justify-content-center g-4 dashboard-grid">

        @php
            $tiles = [
                ['/patrol/foot-summary', 'Foot Patrolling<br>Summary'],
                ['/patrol/night-summary', 'Night Patrolling<br>Summary'],
                ['/attendance/summary', 'Attendance<br>Summary'],
                ['/patrol/foot-explorer', 'Foot Patrolling<br>Explorer'],
                ['/patrol/night-explorer', 'Night Patrolling<br>Explorer'],
                ['/attendance/explorer', 'Attendance<br>Explorer'],
                ['/reports/monthly', 'Monthly<br>Report'],
                ['/reports/camera-tracking', 'Camera &<br>Tracking'],
                ['/patrol/maps', 'KML / Patrol<br>Map'],
            ];
        @endphp

        @foreach($tiles as $i => [$url, $label])
            <div class="col-md-3 col-sm-6">
                <a href="{{ $url }}"
                   class="dash-tile {{ $i % 2 === 0 ? 'tile-green' : 'tile-teal' }}">
                    {!! $label !!}
                </a>
            </div>
        @endforeach

    </div>
</div>

<style>
/* ===============================
   DASHBOARD TYPOGRAPHY
================================ */
.dashboard-title {
    color: #243424;
    letter-spacing: 0.4px;
}

.dashboard-subtitle {
    color: #6c7a6c;
    font-size: 14px;
}

/* ===============================
   DASHBOARD GRID
================================ */
.dashboard-grid {
    margin-top: 34px;
}

/* ===============================
   DASHBOARD TILES (2 COLORS ONLY)
================================ */
.dash-tile {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;

    height: 120px;
    border-radius: 18px;
    padding: 14px;

    font-weight: 600;
    font-size: 15px;
    line-height: 1.4;
    text-decoration: none;
    color: #1f2f1f;

    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    transition: all 0.25s ease;
    position: relative;
}

.dash-tile:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(0,0,0,0.14);
    color: #142314;
}

/* GREEN */
.tile-green {
    background: linear-gradient(135deg, #7eff7e, #c8facc);
}

/* TEAL */
.tile-teal {
    background: linear-gradient(135deg, #79f3eb, #c9fbf7);
}

/* Subtle inner border */
.tile-green::after,
.tile-teal::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 18px;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.35);
}
</style>

@endsection
