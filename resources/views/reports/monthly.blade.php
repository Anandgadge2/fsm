@extends('layouts.app')

@section('content')

<div class="mb-4">
    <h4 class="fw-bold text-dark">Reports Hub</h4>
    <p class="text-muted">Select a report type, apply filters, and export data.</p>
</div>

{{-- Report Type Options --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="{{ url()->current() }}?report_type=attendance" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm {{ $reportType == 'attendance' ? 'ring-2 ring-primary' : '' }}">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-primary"><i class="bi bi-calendar-check fs-1"></i></div>
                    <h5 class="fw-bold text-dark">Attendance Report</h5>
                    <small class="text-muted">Daily attendance logs, entry/exit times</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ url()->current() }}?report_type=patrol" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm {{ $reportType == 'patrol' ? 'ring-2 ring-success' : '' }}">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-success"><i class="bi bi-shield-check fs-1"></i></div>
                    <h5 class="fw-bold text-dark">Patrol Report</h5>
                    <small class="text-muted">All patrol sessions and distances</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ url()->current() }}?report_type=night_patrol" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm {{ $reportType == 'night_patrol' ? 'ring-2 ring-info' : '' }}">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-info"><i class="bi bi-moon-stars fs-1"></i></div>
                    <h5 class="fw-bold text-dark">Night Patrolling Report</h5>
                    <small class="text-muted">Night shift patrols (6 PM - 6 AM)</small>
                </div>
            </div>
        </a>
    </div>
</div>

@if($reportType)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">{{ $title }}</h5>
                <div>
                     <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            

            @if(isset($summary) && $reportType == 'attendance')
                <div class="card mb-4 border shadow-sm">
                    <div class="card-header bg-white fw-bold">Guard Performance Summary</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start ps-3">Guard Name</th>
                                        <th>Total Days</th>
                                        <th>Present</th>
                                        <th>Absent/Leave</th>
                                        <th>Attendance Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $s)
                                        @php
                                            $rate = $s->total_logs > 0 ? ($s->present_days / $s->total_logs) * 100 : 0;
                                            $color = $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger');
                                        @endphp
                                        <tr>
                                            <td class="text-start ps-3 fw-bold">{{ $s->guard_name }}</td>
                                            <td>{{ $s->total_logs }}</td>
                                            <td><span class="badge bg-success">{{ $s->present_days }}</span></td>
                                            <td><span class="badge bg-danger">{{ $s->absent_days }}</span></td>
                                            <td class="fw-bold text-{{ $color }}">{{ round($rate) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            @if($reportType == 'attendance')
                                <th>Date</th>
                                <th>Guard</th>
                                <th>Site</th>
                                <th>Entry</th>
                                <th>Exit</th>
                                <th>Status</th>
                            @else
                                <th>Date & Time</th>
                                <th>Session</th>
                                <th>Guard</th>
                                <th>Site</th>
                                <th>Distance (km)</th>
                                <th>Duration (Hours)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                @if($reportType == 'attendance')
                                    <td>{{ $row->date }}</td>
                                    <td>{{ $row->guard_name }}</td>
                                    <td>{{ $row->site_name }}</td>
                                    <td>{{ $row->entry_time }}</td>
                                    <td>{{ $row->exit_time ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $row->status == 1 ? 'success' : 'danger' }}">
                                            {{ $row->status == 1 ? 'Present' : 'Absent' }}
                                        </span>
                                    </td>
                                @else
                                    <td>{{ \Carbon\Carbon::parse($row->started_at)->format('d M Y, h:i A') }}</td>
                                    <td>{{ $row->session }}</td>
                                    <td>{{ $row->guard_name }}</td>
                                    <td>{{ $row->site_name ?? '—' }}</td>
                                    <td>{{ number_format($row->distance) }} m</td>
                                    <td>
                                        @if($row->ended_at)
                                            {{ \Carbon\Carbon::parse($row->started_at)->diffInMinutes($row->ended_at) }} min
                                        @else
                                            <span class="badge bg-warning text-dark">Ongoing</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No records found for the selected criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<style>
.ring-2 {
    border: 2px solid;
}
.ring-primary { border-color: #0d6efd !important; }
.ring-success { border-color: #198754 !important; }
.ring-info { border-color: #0dcaf0 !important; }
</style>

@endsection
