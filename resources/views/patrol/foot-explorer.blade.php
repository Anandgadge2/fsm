@extends('layouts.app')

@section('content')

<div class="card p-3 mb-4">
    <h5 class="mb-3">Guard-wise Distance Coverage</h5>

    {{-- Horizontal scroll wrapper --}}
    <div style="overflow-x:auto;">
        <div id="guardChartWrapper" style="height:300px;">
            <canvas id="guardDistanceChart"></canvas>
        </div>
    </div>
</div>


<div class="card p-3">
    <h5 class="mb-3">Foot Patrol Explorer</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle smart-sort">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Range</th>
                    <th>Beat</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Distance (KM)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patrols as $p)
                    <tr>
                        <td>{{ $p->user_name }}</td>
                        <td>{{ $p->range ?? '-' }}</td>
                        <td>{{ $p->beat ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->started_at)->format('d M Y, H:i') }}</td>
                        <td>
                            {{ $p->ended_at
                                ? \Carbon\Carbon::parse($p->ended_at)->format('d M Y, H:i')
                                : '-' }}
                        </td>
                        <td>{{ number_format($p->distance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No patrol records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $patrols->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
fetch("{{ route('patrol.foot.guard.distance', request()->query()) }}")
.then(res => res.json())
.then(data => {
    if (!data.length) return;

    const labels = data.map(d => d.guard);
    const values = data.map(d => d.total_distance);

    // 👉 Dynamic width: 60px per guard (tweak if needed)
    const canvas = document.getElementById('guardDistanceChart');
    canvas.width = labels.length * 60;
    canvas.height = 300;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Distance Covered (KM)',
                data: values,
                backgroundColor: '#2f6b4f'
            }]
        },
        options: {
            responsive: false, // 🔑 critical
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => value.toLocaleString()
                    }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.parsed.y.toLocaleString()} KM`
                    }
                }
            }
        }
    });
});
</script>

@endsection
