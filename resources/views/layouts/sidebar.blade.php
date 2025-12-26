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

<style>
/* ===============================
   SIDEBAR LOGO
================================ */

.sidebar-logo {
    width: 100%;
    padding: 0px;
    background: #263526; /* slightly darker for separation */
   
}

.sidebar-logo img {
    width: 100%;
    height: 92px;              /* BIG and readable */
    object-fit: contain;
}




/* Sidebar title tweak */
.sidebar-title {
    color: #ffffff;
    margin-bottom: 10px;
}


   .sidebar {
    height: 100vh;
    width: 240px;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #2f3e2f;
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}
/* Optional: nicer scrollbar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
    background: #4f6f52;
    border-radius: 10px;
}
.sidebar::-webkit-scrollbar-track {
    background: #2f3e2f;
}
.sidebar h5 {
    color: #ffffff;
    border-bottom: 1px solid #445b44;
}

.sidebar-section strong {
    color: #cfe3cf;
    font-size: 13px;
    display: block;
    margin-top: 14px;
}

.sidebar-link {
    display: block;
    padding: 10px 18px;
    color: #b8cbb8;
    text-decoration: none;
    font-size: 14px;
    border-radius: 8px;
    margin: 3px 10px;
}

.sidebar-link:hover {
    background: #3f5640;
    color: #ffffff;
}

.sidebar-link.active {
    background: #4f6f52;
    color: #ffffff;
    font-weight: 600;
}

.sidebar hr {
    border-top: 1px solid #445b44;
    margin: 15px 10px;
}



</style>