@extends('layouts.app')

@section('content')

<div class="row g-3">

    <div class="col-md-6">
        <div class="card p-4 text-center">
            <h6>Total Night Patrols</h6>
            <h2>{{ $totalPatrols }}</h2>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-4 text-center">
            <h6>Total Distance (KM)</h6>
            <h2>{{ $totalDistance }}</h2>
        </div>
    </div>

</div>

@endsection
