@extends('layouts.app')

@section('content')

<div class="container">

    {{-- Header --}}
<div class="text-center dashboard-header">
        <h2 class="fw-bold dashboard-title">Analytics Dashboard</h2>
        <p class="dashboard-subtitle">
            Unified view of patrolling, attendance, and reports
        </p>
    </div>

    {{-- Top Right Logo --}}
<div class="dashboard-logo">
    <img src="{{ asset('images/logo.png') }}" alt="AI Patrolling Logo">
</div>



    {{-- Dashboard Tiles --}}
    <div class="row justify-content-center g-4 dashboard-grid">

        @php
            $tiles = [
                ['/analytics/executive', 'Executive<br>Analytics'],
                ['/patrol/maps', 'KML / Patrol<br>Map'],
                ['/patrol/foot-summary', 'Foot Patrolling<br>Summary'],
                ['/patrol/foot-explorer', 'Foot Patrolling<br>Explorer'],
                ['/patrol/night-summary', 'Night Patrolling<br>Summary'],
                ['/patrol/night-explorer', 'Night Patrolling<br>Explorer'],
                ['/attendance/summary', 'Attendance<br>Summary'],
                ['/attendance/explorer', 'Attendance<br>Explorer'],
                ['/reports/monthly', 'Reports'],
                ['/reports/camera-tracking', 'Camera &<br>Tracking'],
            ];
             $columns = 4; // Since you're using col-md-3 
        @endphp

        @foreach($tiles as $i => [$url, $label])
          @php
        $row = floor($i / $columns);
        $col = $i % $columns;
        
        // Checkerboard logic: if (row + col) is even -> green, odd -> teal
        $colorClass = ($row + $col) % 2 === 0 ? 'tile-green' : 'tile-teal';
    @endphp
            <div class="col-md-3 col-sm-6">
                <a href="{{ $url }}"
                   class="dash-tile {{ $colorClass }}">
                    {!! $label !!}
                </a>
            </div>
        @endforeach

    </div>
</div>

<style>
/* ===============================
   TOP RIGHT LOGO
================================ */
.dashboard-logo {
    position: absolute;
    top: -40px;      /* pull it up */
    right: 32px;
    z-index: 10;
}

.dashboard-logo img {
    height: 68px;
    width: auto;
    object-fit: contain;
}

.container {
    position: relative;
}

.dashboard-logo img {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.dashboard-logo img:hover {
    transform: scale(1.04);
    opacity: 1;
}


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
    margin-top: 24px;
}
/* ===============================
   DASHBOARD HEADER
================================ */
.dashboard-header {
    margin-top: 70px;   /* pushes title below logo */
    margin-bottom: 26px;
}

/* ===============================
   DASHBOARD TILES (2 COLORS ONLY)
================================ */
.dash-tile {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;

    height: 96px;          /* smaller */
    border-radius: 16px;
    padding: 12px;

    font-weight: 600;
    font-size: 14px;       /* tighter */
    line-height: 1.35;
    text-decoration: none;
    color: #1f2f1f;

    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
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
