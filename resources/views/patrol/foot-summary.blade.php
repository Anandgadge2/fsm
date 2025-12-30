@extends('layouts.app')

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <p>Total Sessions</p>
            <h2>{{ $totalSessions }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <p>Completed</p>
            <h2>{{ $completed }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <p>Ongoing</p>
            <h2>{{ $ongoing }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <p>Total Distance (km)</p>
            <h2>{{ number_format($totalDistance,2) }}</h2>
        </div>
    </div>
</div>

<hr>

{{-- Guard-wise summary --}}
<h5 class="mt-4 mb-2 fw-bold">Guard-wise Patrol Summary</h5>

<div class="table-responsive">
<table class="table table-bordered align-middle sortable-table">
<thead>
<tr>
<th data-sortable>Guard</th>
<th data-sortable data-type="number">Sessions</th>
<th data-sortable data-type="number">Completed</th>
<th data-sortable data-type="number">Ongoing</th>
<th data-sortable data-type="number">Distance (km)</th>
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

<div class="mt-3">
    {{ $guardStats->links('pagination::bootstrap-5') }}
</div>

{{-- Charts --}}
{{-- ================= CHARTS ================= --}}


{{-- ROW 2: Distance Coverage by Guard (FULL WIDTH) --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card p-3">
            <h6 class="fw-bold">Distance Coverage by Guard</h6>
            <div style="overflow-x:auto;">
                <div style="min-width:1400px;height:240px;">
                    <canvas id="distanceBar"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: Range-wise + Daily Trend --}}
<div class="row mt-4 g-3 align-items-stretch">
    <div class="col-md-6 d-flex">
        <div class="card p-3 w-100">
            <h6 class="fw-bold">Range-wise Distance Distribution</h6>
            <div style="height:240px;">
                <canvas id="rangeStack"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 d-flex">
        <div class="card p-3 w-100">
            <h6 class="fw-bold">Daily Distance Trend (Last 30 Days)</h6>
            <div style="height:240px;">
                <canvas id="dailyTrend"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{

/* DISTANCE BY GUARD */
new Chart(distanceBar,{type:'bar',data:{
labels:{!! json_encode($guardStats->pluck('guard')) !!},
datasets:[{data:{!! json_encode($guardStats->pluck('total_distance')) !!},
backgroundColor:'#2f6b4f'}]
},options:{responsive:true,maintainAspectRatio:false}});

/* RANGE STACK */
new Chart(rangeStack,{type:'bar',data:{
labels:{!! json_encode($rangeStats->pluck('range_name')) !!},
datasets:[{label:'Distance',
data:{!! json_encode($rangeStats->pluck('distance')) !!},
backgroundColor:'#33691e'}]
}});

/* DAILY TREND */
new Chart(dailyTrend,{type:'line',data:{
labels:{!! json_encode($dailyTrend->pluck('day')) !!},
datasets:[{data:{!! json_encode($dailyTrend->pluck('distance')) !!},
borderColor:'#0d47a1',fill:false}]
}});
});
</script>
@endpush
