@extends('layouts.app')

@section('content')

<div class="card p-3">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Type</th>
                <th>Notes</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
        @foreach($logs as $l)
            <tr>
                <td>{{ $l->type }}</td>
                <td>{{ $l->notes }}</td>
                <td>{{ $l->created_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
