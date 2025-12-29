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
        <table class="table table-bordered align-middle sortable-table">
            <thead>
                <tr>
                    <th data-sortable>Guard</th>
                    <th data-sortable data-type="number">Sessions</th>
                    <th data-sortable data-type="number">Completed</th>
                    <th data-sortable data-type="number">Ongoing</th>
                    <th data-sortable data-type="number">Distance (KM)</th>
                    <th data-sortable data-type="number">KM / Hour</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guardStats as $g)
                <tr>
                    <td>
                        @if(isset($g->user_id))
                            <a href="#" class="guard-name-link" data-guard-id="{{ $g->user_id }}">
                                {{ \App\Helpers\FormatHelper::formatName($g->guard) }}
                            </a>
                        @else
                            {{ \App\Helpers\FormatHelper::formatName($g->guard) }}
                        @endif
                    </td>
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

