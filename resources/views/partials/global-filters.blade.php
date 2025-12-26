@if (!isset($hideFilters) || !$hideFilters)

@php
    // Defensive defaults (prevents 500 errors)
    $ranges = $ranges ?? collect();
    $beats = $beats ?? collect();
    $compartments = $compartments ?? collect();
@endphp

<div class="global-filter-card animate-slide sticky-filter">

<form method="GET" id="globalFilterForm" class="global-filter-grid">

    {{-- RANGE --}}
    <div class="filter-block">
        <label>Range</label>
        <select name="range" id="rangeSelect">
            <option value="">All Ranges</option>

            @foreach($ranges as $id => $name)
                <option value="{{ $id }}" {{ request('range') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- BEAT --}}
    <div class="filter-block">
        <label>Beat</label>
        <select name="beat" id="beatSelect" disabled>
            <option value="">All Beats</option>

            @foreach($beats as $id => $name)
                <option value="{{ $id }}" {{ request('beat') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- COMPARTMENT --}}
    <div class="filter-block">
        <label>Compartment</label>
        <select name="compartment" id="compartmentSelect" disabled>
            <option value="">All Compartments</option>

            @foreach($compartments as $id => $name)
                <option value="{{ $id }}" {{ request('compartment') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- START DATE --}}
    <div class="filter-block">
        <label>Start Date</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}">
    </div>

    {{-- END DATE --}}
    <div class="filter-block">
        <label>End Date</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}">
    </div>

    {{-- ACTIONS --}}
    <div class="filter-actions">
        <button type="submit" class="btn-apply">
            <span class="btn-text">Apply</span>
            <span class="btn-loader d-none"></span>
        </button>
        <a href="{{ url()->current() }}" class="btn-reset">Reset</a>
    </div>

</form>
</div>
@endif
