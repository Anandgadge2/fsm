@extends('layouts.app')

@section('content')

<h5 class="fw-bold mb-3">Foot Patrol Defaulters</h5>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Guard</th>
            <th>Total Patrols</th>
            <th>Total Distance (KM)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($defaulters as $d)
        <tr>
            <td>{{ $d->name }}</td>
            <td>{{ $d->patrols }}</td>
            <td>{{ round($d->distance,2) }}</td>
            <td class="text-danger fw-bold">Defaulter</td>
        </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('export.foot.pdf') }}" class="btn btn-danger">Export PDF</a>
<a href="{{ route('export.foot.excel') }}" class="btn btn-success">Export Excel</a>

@endsection
