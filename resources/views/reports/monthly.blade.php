@extends('layouts.app')

@section('content')

<div class="card p-3">
    <canvas id="monthlyChart"></canvas>
</div>

<script>
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: @json($monthly->pluck('month')),
        datasets: [{
            label: 'Distance Covered (KM)',
            data: @json($monthly->pluck('total_distance')),
            backgroundColor: '#81c784'
        }]
    }
});
</script>

@endsection
