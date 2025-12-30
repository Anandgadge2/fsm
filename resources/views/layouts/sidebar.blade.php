<div class="sidebar">
    
    {{-- Sidebar Logo --}}
<div class="sidebar-logo">
    <img src="{{ asset('images/logo1.png') }}" alt="AI Patrolling Logo">
</div>

<h5 class="p-3 text-center sidebar-title">AI Patrolling</h5>


    {{-- MAIN DASHBOARD --}}
    <a href="/"
       class="sidebar-link {{ request()->routeIs('/') || request()->is('/') ? 'active' : '' }}">
        Main Dashboard
    </a>

    <hr>

    {{-- FOOT PATROLLING --}}
    <div class="sidebar-section {{ request()->is('patrol/foot*') ? 'open' : '' }}">
        <strong class="px-3">Foot Patrolling</strong>

        <a href="/patrol/foot-summary"
           class="sidebar-link {{ request()->is('patrol/foot-summary') ? 'active' : '' }}">
            Summary
        </a>

        <a href="/patrol/foot-explorer"
           class="sidebar-link {{ request()->is('patrol/foot-explorer') ? 'active' : '' }}">
            Explorer
        </a>
    </div>

    {{-- NIGHT PATROLLING --}}
    <div class="sidebar-section {{ request()->is('patrol/night*') ? 'open' : '' }}">
        <strong class="px-3 mt-2">Night Patrolling</strong>

        <a href="/patrol/night-summary"
           class="sidebar-link {{ request()->is('patrol/night-summary') ? 'active' : '' }}">
            Summary
        </a>

        <a href="/patrol/night-explorer"
           class="sidebar-link {{ request()->is('patrol/night-explorer') ? 'active' : '' }}">
            Explorer
        </a>
    </div>

    {{-- KML/PATROL MAP --}}
    <a href="/patrol/maps"
       class="sidebar-link {{ request()->is('patrol/maps') ? 'active' : '' }}">
        KML/Patrol Map
    </a>

    {{-- ATTENDANCE --}}
    <div class="sidebar-section {{ request()->is('attendance*') ? 'open' : '' }}">
        <strong class="px-3 mt-2">Attendance</strong>

        <a href="/attendance/summary"
           class="sidebar-link {{ request()->is('attendance/summary') ? 'active' : '' }}">
            Summary
        </a>

        <a href="/attendance/explorer"
           class="sidebar-link {{ request()->is('attendance/explorer') ? 'active' : '' }}">
            Explorer
        </a>
    </div>

    <hr>

  {{-- INCIDENTS --}}
<div class="sidebar-section {{ request()->is('incidents*') ? 'open' : '' }}">
    <strong class="px-3 mt-2">Incidents</strong>

    <a href="/incidents/summary"
       class="sidebar-link {{ request()->is('incidents/summary') ? 'active' : '' }}">
        Summary
    </a>

    <a href="/incidents/explorer"
       class="sidebar-link {{ request()->is('incidents/explorer') ? 'active' : '' }}">
        Explorer
    </a>
</div>


    {{-- REPORTS --}}
    <div class="sidebar-section {{ request()->is('reports*') ? 'open' : '' }}">
        <strong class="px-3">Reports</strong>

        <a href="/reports/monthly"
           class="sidebar-link {{ request()->is('reports/monthly') ? 'active' : '' }}">
            Monthly Report
        </a>

        <a href="/reports/camera-tracking"
           class="sidebar-link {{ request()->is('reports/camera-tracking') ? 'active' : '' }}">
            Camera & Tracking
        </a>

        <a href="/reports/foot-report"
           class="sidebar-link {{ request()->is('reports/foot-report') ? 'active' : '' }}">
            Foot Patrolling Report
        </a>

        <a href="/reports/night-report"
           class="sidebar-link {{ request()->is('reports/night-report') ? 'active' : '' }}">
            Night Patrolling Report
        </a>
    </div>
</div>
