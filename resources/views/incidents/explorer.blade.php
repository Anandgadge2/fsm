@extends('layouts.app')

@section('content')

@php
    function severityBadge($s) {
        return match($s) {
            5 => 'danger',
            4 => 'warning',
            3 => 'primary',
            2 => 'success',
            default => 'secondary'
        };
    }
@endphp

{{-- ================= HEADER ================= --}}
<div class="mb-3">
    <h5 class="fw-bold mb-0">Incident Explorer</h5>
    <small class="text-muted">Operational incident intelligence & drill-down</small>
</div>

{{-- ================= LATEST 10 INCIDENTS ================= --}}
<div class="card mb-4">
    <div class="card-header fw-semibold">Latest 10 Incidents</div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Type</th>
                <th>Severity</th>
                <th>Guard</th>
                <th>Range</th>
                <th>Beat</th>
                <th>Compartment</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($latestTop10 as $i)
                <tr onclick="openIncident({{ json_encode($i) }})" style="cursor:pointer">
                    <td><span class="badge bg-secondary">{{ $i->type }}</span></td>
                    <td>
                        <span class="badge bg-{{ severityBadge($i->severity) }}">
                            {{ $i->severity }}
                        </span>
                    </td>
                    <td>{{ $i->guard ?? '—' }}</td>
                    <td>{{ $i->range_id ?? '—' }}</td>
                    <td>{{ $i->beat_id ?? '—' }}</td>
                    <td class="fw-semibold">{{ $i->compartment ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($i->created_at)->format('d M Y, h:i A') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ================= CHARTS ================= --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-2">Incidents by Type</h6>
            <div style="height:260px">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-2">Incidents by Patrol Mode</h6>
            <div style="height:260px">
                <canvas id="sessionChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ================= ALL INCIDENTS ================= --}}
<div class="card">
    <div class="card-header fw-semibold">All Incidents</div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Type</th>
                <th>Severity</th>
                <th>Guard</th>
                <th>Range</th>
                <th>Beat</th>
                <th>Compartment</th>
                <th>Session</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($incidents as $i)
                <tr onclick="openIncident({{ json_encode($i) }})" style="cursor:pointer">
                    <td><span class="badge bg-secondary">{{ $i->type }}</span></td>
                    <td>
                        <span class="badge bg-{{ severityBadge($i->severity) }}">
                            {{ $i->severity }}
                        </span>
                    </td>
                    <td>{{ $i->guard ?? '—' }}</td>
                    <td>{{ $i->range_id ?? '—' }}</td>
                    <td>{{ $i->beat_id ?? '—' }}</td>
                    <td class="fw-semibold">{{ $i->compartment ?? '—' }}</td>
                    <td>{{ $i->session ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($i->created_at)->format('d M Y, h:i A') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-2 d-flex justify-content-end">
        {{ $incidents->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- ================= MODAL ================= --}}
<div id="incidentModal" class="incident-modal">
    <div class="incident-modal-content">
        <span class="close-btn" onclick="closeIncident()">×</span>
        <h6 class="fw-bold mb-2">Incident Details</h6>
        <div id="incidentDetails" class="small"></div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function openIncident(data) {
    document.getElementById('incidentDetails').innerHTML = `
        <p><strong>Type:</strong> ${data.type}</p>
        <p><strong>Severity:</strong> ${data.severity}</p>
        <p><strong>Guard:</strong> ${data.guard ?? '-'}</p>
        <p><strong>Range:</strong> ${data.range_id ?? '-'}</p>
        <p><strong>Beat:</strong> ${data.beat_id ?? '-'}</p>
        <p><strong>Compartment:</strong> ${data.compartment ?? '-'}</p>
        <p><strong>Session:</strong> ${data.session ?? '-'}</p>
        <p><strong>Date:</strong> ${new Date(data.created_at).toLocaleString()}</p>
        <hr>
        <p><strong>Notes:</strong><br>${data.notes ?? '—'}</p>
    `;
    document.getElementById('incidentModal').classList.add('show');
}

function closeIncident() {
    document.getElementById('incidentModal').classList.remove('show');
}

/* Charts */
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($typeStats->pluck('type')) !!},
        datasets: [{
            data: {!! json_encode($typeStats->pluck('total')) !!},
            backgroundColor: ['#2e7d32','#1e88e5','#f9a825','#c62828']
        }]
    },
    options: {
        maintainAspectRatio:false,
        cutout:'60%',
        plugins:{ legend:{ position:'bottom' }}
    }
});

new Chart(document.getElementById('sessionChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($sessionStats->pluck('session')) !!},
        datasets: [{
            data: {!! json_encode($sessionStats->pluck('total')) !!},
            backgroundColor:'#1565c0',
            borderRadius:6
        }]
    },
    options:{
        maintainAspectRatio:false,
        plugins:{ legend:{ display:false }},
        scales:{ y:{ beginAtZero:true }}
    }
});
</script>

<style>
.incident-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    display: none;
    z-index: 9999;
}
.incident-modal.show {
    display: flex;
    justify-content: center;
    align-items: center;
}
.incident-modal-content {
    background: #fff;
    width: 520px;
    max-height: 80vh;
    overflow-y: auto;
    padding: 20px;
    border-radius: 14px;
    position: relative;
}
.close-btn {
    position:absolute;
    top:10px;
    right:14px;
    font-size:22px;
    cursor:pointer;
}
</style>
@endpush
