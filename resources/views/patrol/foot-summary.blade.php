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
<table class="table table-bordered align-middle smart-sort">
<thead class="table-light ">
<tr >
<th class="cursor-pointer">Guard</th>
<th>Sessions</th>
<th>Completed</th>
<th>Ongoing</th>
<th>Distance (km)</th>
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

{{-- Charts --}}
{{-- ================= CHARTS ================= --}}

{{-- ROW 1: Top 10 Guards + Patrol Status --}}
<div class="row mt-4 g-3 align-items-stretch">
    <div class="col-md-8 d-flex">
        <div class="card p-3 w-100">
            <h6 class="fw-bold">Top 10 Guards by Distance</h6>
            <div style="height:240px;">
                <canvas id="topGuards"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4 d-flex">
        <div class="card p-3 w-100">
            <h6 class="fw-bold">Patrol Status</h6>
            <div style="height:240px;">
                <canvas id="statusPie"></canvas>
            </div>
        </div>
    </div>
</div>

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

/* PIE */
new Chart(statusPie,{type:'pie',data:{
labels:['Completed','Ongoing','Incomplete'],
datasets:[{data:[
{{ $statusPie['completed'] }},
{{ $statusPie['ongoing'] }},
{{ $statusPie['incomplete'] }}
],backgroundColor:['#2e7d32','#f9a825','#c62828']}]
}});

/* TOP 10 */
new Chart(topGuards,{type:'bar',data:{
labels:{!! json_encode($topGuards->pluck('guard')) !!},
datasets:[{data:{!! json_encode($topGuards->pluck('total_distance')) !!},
backgroundColor:'#1b5e20'}]
}});

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
