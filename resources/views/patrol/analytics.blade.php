@extends('layouts.app')

@section('content')

<h4 class="mb-3">Foot Patrol Analytics</h4>

{{-- ================= TABLE ================= --}}
<div class="card p-3 mb-4">
    <table class="table table-bordered align-middle smart-sort">
        <thead class="table-light">
            <tr>
                <th>Guard</th>
                <th>Total Sessions</th>
                <th>Completed</th>
                <th>Ongoing</th>
                <th>Total Distance (km)</th>
                <th>Avg Distance (km)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guards as $g)
            <tr>
                <td>{{ $g->guard }}</td>
                <td>{{ $g->total_sessions }}</td>
                <td class="text-success">{{ $g->completed }}</td>
                <td class="text-warning">{{ $g->ongoing }}</td>
                <td>{{ $g->total_distance }}</td>
                <td>{{ $g->avg_distance }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ================= CHARTS ================= --}}
<div class="row g-4">
    <div class="col-md-7">
        <div class="card p-3">
            <h6>Total Distance by Guard</h6>
            <canvas id="distanceChart"
                data-labels='@json($guards->pluck("guard"))'
                data-values='@json($guards->pluck("total_distance"))'>
            </canvas>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card p-3">
            <h6>Session Status</h6>
            <canvas id="statusChart"
                data-values='@json([$status->completed,$status->ongoing,$status->incomplete])'>
            </canvas>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* ===== BAR CHART ===== */
const d = document.getElementById('distanceChart');
new Chart(d, {
    type: 'bar',
    data: {
        labels: JSON.parse(d.dataset.labels),
        datasets: [{
            label: 'Distance (km)',
            data: JSON.parse(d.dataset.values),
            backgroundColor: '#2e7d32'
        }]
    },
    options: { responsive:true }
});

/* ===== PIE CHART ===== */
const s = document.getElementById('statusChart');
new Chart(s, {
    type: 'pie',
    data: {
        labels: ['Completed','Ongoing','Incomplete'],
        datasets: [{
            data: JSON.parse(s.dataset.values),
            backgroundColor: ['#4caf50','#ff9800','#e53935']
        }]
    }
});
</script>
@endpush
