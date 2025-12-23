@extends('layouts.app')

@section('content')

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-kpi p-3 text-center">
            <h6>Total Distance (KM)</h6>
            <h3>5065</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-kpi p-3 text-center">
            <h6>Total Patrols</h6>
            <h3>42</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-kpi p-3 text-center">
            <h6>Total Duration</h6>
            <h3>2275:55</h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card p-3">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <canvas id="patrolChart"></canvas>
        </div>
    </div>
</div>

@endsection
