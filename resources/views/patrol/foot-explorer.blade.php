@extends('layouts.app')

@section('content')

<div class="card p-3">

    <h5 class="mb-3">Foot Patrol Explorer</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Range</th>
                    <th>Beat</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Distance (KM)</th>
                </tr>
            </thead>

            <tbody>
                @forelse($patrols as $p)
                    <tr>
                        <td>{{ $p->user_name }}</td>
                        <td>{{ $p->range ?? '-' }}</td>
                        <td>{{ $p->beat ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->started_at)->format('d M Y, H:i') }}</td>
                        <td>
                            {{ $p->ended_at 
                                ? \Carbon\Carbon::parse($p->ended_at)->format('d M Y, H:i') 
                                : '-' }}
                        </td>
                        <td>{{ number_format($p->distance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No patrol records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $patrols->links() }}
    </div>

</div>

@endsection
