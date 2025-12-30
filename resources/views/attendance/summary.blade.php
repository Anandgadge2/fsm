@extends('layouts.app')

@section('content')

{{-- ================= KPI ================= --}}
<div class="row g-3 mb-4 fade-in">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Present Guards</h6>
                        <h2 class="mb-0 fw-bold text-success">{{ $present }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem;">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Absent Guards</h6>
                        <h2 class="mb-0 fw-bold text-danger">{{ $absent }}</h2>
                    </div>
                    <div class="text-danger" style="font-size: 2.5rem;">
                        <i class="bi bi-person-dash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Guards</h6>
                        <h2 class="mb-0 fw-bold text-primary">{{ $total }}</h2>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
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
            <h6 class="fw-bold mb-3">
                Daily Attendance 
                <small class="text-muted fw-normal ms-2" style="font-size: 0.75rem;">(Click bars to view guard list)</small>
            </h6>

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
            <table class="table table-bordered table-hover table-sm mb-0 sortable-table">
                <thead>
                    <tr>
                        <th data-sortable>Guard</th>
                        <th data-sortable data-type="number" class="text-center">Days Present</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topAttendance as $t)
                    <tr>
                        <td>
                            @php
                                $guardUser = DB::table('users')->where('name', $t->name)->first();
                            @endphp
                            @if($guardUser)
                                <a href="#" class="guard-name-link" data-guard-id="{{ $guardUser->id }}">
                                    {{ \App\Helpers\FormatHelper::formatName($t->name) }}
                                </a>
                            @else
                                {{ \App\Helpers\FormatHelper::formatName($t->name) }}
                            @endif
                        </td>
                        <td class="fw-bold text-success text-center">{{ $t->days_present }}</td>
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
            <table class="table table-bordered table-hover table-sm mb-0 sortable-table">
                <thead>
                    <tr>
                        <th data-sortable>Guard</th>
                        <th data-sortable data-type="number" class="text-center">Days Present</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($defaulters as $d)
                    <tr>
                        <td>
                            @php
                                $guardUser = DB::table('users')->where('name', $d->name)->first();
                            @endphp
                            @if($guardUser)
                                <a href="#" class="guard-name-link" data-guard-id="{{ $guardUser->id }}">
                                    {{ \App\Helpers\FormatHelper::formatName($d->name) }}
                                </a>
                            @else
                                {{ \App\Helpers\FormatHelper::formatName($d->name) }}
                            @endif
                        </td>
                        <td class="fw-bold text-danger text-center">{{ $d->days_present }}</td>
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

{{-- ================= MODAL ================= --}}
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="attnModalLabel">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush" id="attnList"></ul>
            </div>
        </div>
    </div>
</div>

@include('partials.guard-detail-modal')

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
            backgroundColor: ['#43a047', '#e57373'],
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
const dailyData = {!! json_encode($daily) !!};

new Chart(barChart, {
    type: 'bar',
    data: {
        labels: dailyData.map(d => d.date),
        datasets: [
            {
                label: 'Present',
                data: dailyData.map(d => d.present),
                backgroundColor: '#43a047',
                hoverbackgroundColor: '#2e7d32',
                barPercentage: 0.7,
                categoryPercentage: 0.8,
                borderRadius: 4
            },
            {
                label: 'Absent',
                data: dailyData.map(d => d.absent),
                backgroundColor: '#e57373',
                hoverBackgroundColor: '#ef5350',
                barPercentage: 0.7,
                categoryPercentage: 0.8,
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        onClick: (event, elements, chart) => {
            if (!elements.length) return;

            const i = elements[0].index;
            const dsIndex = elements[0].datasetIndex; // 0 = Present, 1 = Absent
            const d = dailyData[i];

            const isPresent = dsIndex === 0;
            const list = isPresent ? d.present_list : d.absent_list;
            const title = isPresent 
                ? `Present on ${d.date} (${d.present})` 
                : `Absent on ${d.date} (${d.absent})`;
            
            const listEl = document.getElementById('attnList');
            const titleEl = document.getElementById('attnModalLabel');
            
            titleEl.textContent = title;
            titleEl.className = 'modal-title fw-bold ' + (isPresent ? 'text-success' : 'text-danger');
            
            listEl.innerHTML = list.length 
                ? list.map(u => `
                    <li class="list-group-item py-2">
                        <span class="guard-name-link text-decoration-underline text-primary" 
                              style="cursor:pointer" 
                              data-guard-id="${u.id}">
                            ${u.name}
                        </span>
                    </li>
                `).join('') 
                : `<li class="list-group-item text-muted text-center">No guards list available</li>`;

            new bootstrap.Modal(document.getElementById('attendanceModal')).show();
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    maxTicksLimit: 12,
                    maxRotation: 0,
                    font: { size: 11 }
                }
            },
            y: {
                beginAtZero: true,
                grid: { borderDash: [4, 4], color: '#f0f0f0' },
                title: { display: true, text: 'Guards' }
            }
        },
        plugins: {
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                cornerRadius: 8,
                displayColors: true
            },
            legend: {
                position: 'top',
                align: 'end',
                labels: { usePointStyle: true, boxWidth: 8 }
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
.modal-backdrop{
        z-index: 1 !important;
    }
</style>
