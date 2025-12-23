@extends('layouts.app')

@section('content')

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card kpi green">
            <h6>Present</h6>
            <h2>{{ $present }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card kpi red">
            <h6>Absent</h6>
            <h2>{{ $absent }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card kpi dark">
            <h6>Total Records</h6>
            <h2>{{ $total }}</h2>
        </div>
    </div>
</div>

{{-- CHARTS --}}
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="fw-bold mb-2">Present vs Absent</h6>
            <canvas id="attendancePie"
                data-present="{{ $present }}"
                data-absent="{{ $absent }}">
            </canvas>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-3">
            <h6 class="fw-bold mb-2">Daily Attendance Trend</h6>
            <canvas id="attendanceBar"
    data-labels='@json($daily?->pluck("date") ?? [])'
    data-present='@json($daily?->pluck("present") ?? [])'
    data-absent='@json($daily?->pluck("absent") ?? [])'>
</canvas>

        </div>
    </div>
</div>

{{-- STYLES --}}
<style>
.kpi {
    padding: 20px;
    border-radius: 14px;
    text-align: center;
    color: #fff;
    font-weight: 600;
}
.kpi.green { background:#2e7d32; }
.kpi.red { background:#c62828; }
.kpi.dark { background:#37474f; }
</style>

{{-- CHART JS --}}
<script>
const pie = document.getElementById('attendancePie');
new Chart(pie, {
    type: 'pie',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            data: [
                pie.dataset.present,
                pie.dataset.absent
            ],
            backgroundColor: ['#2e7d32', '#c62828']
        }]
    }
});

const bar = document.getElementById('attendanceBar');
new Chart(bar, {
    type: 'bar',
    data: {
        labels: JSON.parse(bar.dataset.labels),
        datasets: [
            {
                label: 'Present',
                data: JSON.parse(bar.dataset.present),
                backgroundColor: '#2e7d32'
            },
            {
                label: 'Absent',
                data: JSON.parse(bar.dataset.absent),
                backgroundColor: '#c62828'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

@endsection
