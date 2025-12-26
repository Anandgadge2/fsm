@extends('layouts.app')

@section('content')

{{-- KPI ROW --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <small>Total Sessions</small>
            <h4 class="fw-bold">{{ $totalSessions }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <small>Completed</small>
            <h4 class="fw-bold text-success">{{ $completed }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <small>Ongoing</small>
            <h4 class="fw-bold text-warning">{{ $ongoing }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <small>Total Distance (KM)</small>
            <h4 class="fw-bold">{{ number_format($totalDistance,2) }}</h4>
        </div>
    </div>
</div>

{{-- GUARD TABLE --}}
<div class="card p-3 mb-4">
    <h6 class="fw-bold mb-2">Guard-wise Night Patrol Summary</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Guard</th>
                    <th>Sessions</th>
                    <th>Completed</th>
                    <th>Ongoing</th>
                    <th>Distance (KM)</th>
                    <th>KM / Hour</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guardStats as $g)
                <tr>
                    <td>{{ $g->guard }}</td>
                    <td>{{ $g->total_sessions }}</td>
                    <td class="text-success">{{ $g->completed }}</td>
                    <td class="text-warning">{{ $g->ongoing }}</td>
                    <td>{{ number_format($g->total_distance,2) }}</td>
                    <td>{{ number_format($g->km_per_hour ?? 0,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $guardStats->links('pagination::bootstrap-5') }}
</div>

{{-- GRAPHS --}}
<div class="row g-3 mt-3">
    {{-- TOP 5 --}}
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold text-center mb-2">
                Top 5 Guards by Distance (Night)
            </h6>
            <div style="height:240px;">
                <canvas id="topNightGuards"></canvas>
            </div>
        </div>
    </div>

    {{-- STATUS --}}
    <div class="col-md-6">
        <div class="card p-3 h-100 d-flex flex-column">
            <h6 class="fw-bold text-center mb-2">
                Night Patrol Status
            </h6>
            <div class="d-flex justify-content-center align-items-center flex-grow-1">
                <div style="width:220px;height:220px;">
                    <canvas id="nightStatusPie"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- SPEED GRAPH --}}
<div class="card p-3 mt-4">
    <h6 class="fw-bold text-center mb-2">
        Guard Patrol Speed (KM/H)
    </h6>

    <div style="overflow-x:auto;">
        <div style="min-width:1600px;height:300px;">
            <canvas id="speedChart"></canvas>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* ========== TOP 5 NIGHT GUARDS ========== */
new Chart(document.getElementById('topNightGuards'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($topGuards->pluck('guard')) !!},
        datasets: [{
            data: {!! json_encode($topGuards->pluck('distance')) !!},
            backgroundColor: '#1b5e20',
            barThickness: 46
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

/* ========== NIGHT STATUS PIE ========== */
new Chart(document.getElementById('nightStatusPie'), {
    type: 'pie',
    data: {
        labels: ['Completed','Ongoing','Incomplete'],
        datasets: [{
            data: [
                {{ $statusPie['completed'] }},
                {{ $statusPie['ongoing'] }},
                {{ $statusPie['incomplete'] }}
            ],
            backgroundColor: ['#2e7d32','#f9a825','#c62828']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' }
        }
    }
});

/* ========== SPEED CHART (SCROLLABLE + THICK BARS) ========== */
new Chart(document.getElementById('speedChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($speedStats->pluck('guard')) !!},
        datasets: [{
            label: 'KM / Hour',
            data: {!! json_encode($speedStats->pluck('speed')) !!},
            backgroundColor: '#1565c0',
            barThickness: 22
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush

