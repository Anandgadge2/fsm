@extends('layouts.app')

@section('content')

<div class="attendance-explorer-wrapper">

    {{-- HEADER --}}
    <div class="explorer-header">
        <h4>Attendance Explorer</h4>

        <form method="GET">
            <select name="month" onchange="this.form.submit()" class="month-select">
                @foreach($months as $m)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($m.'-01')->format('F Y') }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="explorer-layout">

        {{-- ================= TABLE ONLY SCROLLS ================= --}}
        <div class="dot-table-wrapper">
            <div class="dot-table-scroll">
                <table class="dot-table smart-sort">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Range</th>
                            <th>Beat</th>
                            <th>Compartment</th>
                            <th>Total</th>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                <th data-type="number">{{ $d }}</th>
                            @endfor
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($grid as $data)
                        @php $user = $data['user']; @endphp
                        <tr>
                            <td class="user-cell">
                                <img src="{{ asset($user->profile_pic ?? 'images/user-placeholder.png') }}" class="user-avatar">
                                <span class="user-name">{{ $user->name }}</span>
                            </td>

                           <td>{{ $data['meta']['range'] ?? '-' }}</td>
                           <td>{{ $data['meta']['beat'] ?? '-' }}</td>
                           <td>{{ $data['meta']['compartment'] ?? '-' }}</td>


                            <td class="fw-semibold">
                                {{ $data['summary']['present'] }} / {{ $data['summary']['total'] }}
                            </td>

                            @for($d = 1; $d <= $daysInMonth; $d++)
                                <td>
                                    <span class="dot {{ $data['days'][$d]['present'] ? 'present' : 'absent' }}"></span>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= KPI PANEL ================= --}}
        <div class="kpi-panel">
            <div class="kpi kpi-green">
                <p>Present %</p>
                <h2>{{ $presentPct }}%</h2>
            </div>

            <div class="kpi kpi-blue">
                <p>Total Present</p>
                <h2>{{ $totalPresent }}</h2>
            </div>

            <div class="kpi kpi-red">
                <p>Total Absent</p>
                <h2>{{ $totalAbsent }}</h2>
            </div>

            <div class="kpi kpi-grey">
                <p>Total Days</p>
                <h2>{{ $totalDays }}</h2>
            </div>
        </div>

    </div>
</div>

@endsection



{{-- STYLES --}}
<style>
/* ===============================
   LAYOUT
================================ */
.attendance-explorer-wrapper {
    padding: 10px 6px;
}

.explorer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.explorer-header h4 {
    font-weight: 700;
    color: #2f3e2f;
}

.month-select {
    padding: 6px 12px;
    border-radius: 10px;
    border: 1px solid #dcdcdc;
}
.month-select:focus {
    outline: none;
    border-color: #4f6f52;
    background: #ffffff;
}

/* ===============================
   MAIN LAYOUT
================================ */
.explorer-layout {
    display: flex;
    gap: 18px;
}

/* ===============================
   TABLE WRAPPER
================================ */

.dot-table-wrapper {
    flex: 1;
    overflow-x: auto;
    position: relative;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}

.dot-table-scroll {
    height: 70vh;              /* controls vertical scroll */
    overflow: auto;          /* vertical scroll */
    position: relative;
    background: #ffffff;
    border-radius: 12px;
}

/* ===============================
TABLE BASE
================================ */

.dot-table {
    border-collapse: separate;
    border-spacing: 0;
    width: max-content;
    font-size: 14px;
}

.dot-table th,
.dot-table td {
    white-space: nowrap;
}
.dot-table thead th:first-child {
    z-index: 10;
    background: #eef2ee;
}

/* ===============================
   USER COLUMN (STICKY LEFT)
================================ */
.dot-table th:first-child,
.dot-table td:first-child {
    position: sticky;
    left: 0;
    background: #ffffff;
    z-index: 6;
    min-width: 240px;
    box-shadow: 2px 0 8px rgba(0,0,0,0.06);
     border-right: 1px solid #e2e6e2;
}

.dot-table td:nth-child(5),
.dot-table th:nth-child(5) {
    min-width: 110px;
    background: #f9fbf9;
    font-weight: 600;
}


.dot-table th:nth-child(n+5),
.dot-table td:nth-child(n+5) {
    min-width: 32px;
}

/* Header needs higher z-index */
.dot-table thead th {
     position: sticky;
    top: 0;
    background: #f6f8f6;
    z-index: 5;
    font-weight: 600;
}

/* ===============================
   COLUMN SIZES
================================ */
.dot-table th:nth-child(n+2),
.dot-table td:nth-child(n+2) {
    min-width: 32px;
    text-align: center;
}

/* ===============================
   HEADER (STICKY)
================================ */
.dot-table thead th {
    position: sticky;
    top: 0;
    background: #f6f8f6;
    z-index: 5;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 12px;
    border-bottom: 1px solid #e2e6e2;
}

.dot-table td {
     white-space: nowrap;
    padding: 8px 10px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
}
.dot-table tr:hover td {
    background: #fafcf9;
}
.dot-table tr:hover {
    background: #fafafa;
}

/* User cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e6ebe6;
}

.user-name {
    font-weight: 600;
    color: #2e2e2e;
}

/* Dots */
.dot {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    display: inline-block;
}

.dot.present { background: #30a034; }
.dot.absent  { background: #ffb1af; }
.dot.empty   { background: #dcdcdc; }

/* KPI Panel */
.kpi-panel {
    width: 240px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.kpi {
    border-radius: 16px;
    padding: 18px;
    text-align: center;
        border: 1px solid #e3e8e3;
font-weight: 600;
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

.kpi p {
    margin: 0;
    font-weight: 600;
    font-size: 13px;
    opacity: 0.9;
}

.kpi h2 {
    margin-top: 6px;
    font-size: 28px;
    font-weight: 800;
}

/* KPI Color Themes */
.kpi-green {
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        border-color: #b7dfbf;

    color: #1b5e20;
}

.kpi-blue {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-color: #bbdefb;
    color: #0d47a1;
}

.kpi-red {
    background: linear-gradient(135deg, #fdecea, #f8c7c3);
     border-color: #f5b7b1;
    color: #b71c1c;
}

.kpi-grey {
    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
     border-color: #d6dad6;
    color: #424242;
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
