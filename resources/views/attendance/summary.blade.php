@extends('layouts.app')

@section('content')

{{-- ================= KPI ================= --}}
<div class="row g-3 mb-4 fade-in">
    <div class="col-md-4">
        <div class="kpi kpi-green">
            <small>Present Guards</small>
            <h2>{{ $present }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi kpi-red">
            <small>Absent Guards</small>
            <h2>{{ $absent }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi kpi-dark">
            <small>Total Guards</small>
            <h2>{{ $total }}</h2>
        </div>
    </div>
</div>

{{-- ================= PIE + DAILY BAR ================= --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card chart-box">
            <h6 class="fw-bold text-center mb-3">Present vs Absent</h6>
            <div class="chart-fixed">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card chart-box">
            <h6 class="fw-bold mb-3">Daily Attendance</h6>

            <div class="chart-scroll-x">
                <div class="chart-wide">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= TABLES ================= --}}
<div class="row g-4 mb-4 fade-in">
    <div class="col-md-6">
        <div class="card table-card">
            <div class="card-header text-center fw-semibold">
                Top 10 Attendance
            </div>
            <table class="table table-bordered table-hover table-sm text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Guard</th>
                        <th>Days Present</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topAttendance as $t)
                    <tr>
                        <td>{{ $t->name }}</td>
                        <td class="fw-bold text-success">{{ $t->days_present }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card table-card">
            <div class="card-header text-center fw-semibold">
                Top 10 Defaulters
            </div>
            <table class="table table-bordered table-hover table-sm text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Guard</th>
                        <th>Days Present</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($defaulters as $d)
                    <tr>
                        <td>{{ $d->name }}</td>
                        <td class="fw-bold text-danger">{{ $d->days_present }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= GUARD WISE ================= --}}
<div class="card chart-box fade-in">
    <h6 class="fw-bold text-center mb-3">Guard-wise Attendance</h6>

    <div class="chart-scroll-x">
        <div class="chart-wide">
            <canvas id="guardChart"></canvas>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* ================= PIE ================= */
new Chart(pieChart, {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            data: [{{ $present }}, {{ $absent }}],
            backgroundColor: ['#2e7d32', '#f6b1b1'],
            borderWidth: 0
        }]
    },
    options: {
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

/* ================= DAILY BAR ================= */
new Chart(barChart, {
    type: 'bar',
    data: {
        labels: {!! json_encode($daily->pluck('date')) !!},
        datasets: [
            {
                label: 'Present',
                data: {!! json_encode($daily->pluck('present')) !!},
                backgroundColor: '#2e7d32',
                barThickness: 26
            },
            {
                label: 'Absent',
                data: {!! json_encode($daily->pluck('absent')) !!},
                backgroundColor: '#f6b1b1',
                barThickness: 26
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                ticks: {
                    maxTicksLimit: 10,
                    maxRotation: 45,
                    minRotation: 45
                },
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Guards'
                }
            }
        }
    }
});

/* ================= GUARD LINE ================= */
new Chart(guardChart, {
    type: 'line',
    data: {
        labels: {!! json_encode($guardAttendance->pluck('name')) !!},
        datasets: [{
            label: 'Days Present',
            data: {!! json_encode($guardAttendance->pluck('days_present')) !!},
            fill: true,
            backgroundColor: 'rgba(46,125,50,0.2)',
            borderColor: '#2e7d32',
            pointRadius: 4,
            tension: 0.35
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Days Present'
                }
            }
        }
    }
});
</script>
@endpush

<style>
/* ================= KPI ================= */
.kpi {
    padding: 26px;
    border-radius: 18px;
    text-align: center;
    color: #fff;
    box-shadow: 0 12px 28px rgba(0,0,0,.08);
}
.kpi-green { background:#2e7d32; }
.kpi-red   { background:#f6b1b1; color:#222; }
.kpi-dark  { background:#455a64; }

/* ================= CHART LAYOUT ================= */
.chart-box {
    padding: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,.06);
}

.chart-fixed {
    height: 260px;
}

.chart-scroll-x {
    overflow-x: auto;
}

.chart-wide {
    width: max-content;
    min-width: 1200px;
    height: 320px;
}

/* ================= TABLE ================= */
.table-card {
    box-shadow: 0 8px 20px rgba(0,0,0,.05);
}

/* ================= ANIMATION ================= */
.fade-in {
    animation: fadeUp .6s ease both;
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
</style>
