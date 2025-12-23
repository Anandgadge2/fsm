@if (!isset($hideFilters) || !$hideFilters)
<div class="global-filter-card">
    <form method="GET" id="globalFilterForm" class="global-filter-grid">

        <div class="filter-block">
            <label>Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>

        <div class="filter-block">
            <label>End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>

        <div class="filter-block">
            <label>Range</label>
            <select name="range" id="rangeSelect">
                <option value="">All Ranges</option>
                @foreach($ranges as $r)
                    <option value="{{ $r->client_name }}" {{ request('range') == $r->client_name ? 'selected' : '' }}>
                        {{ $r->client_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-block">
            <label>Beat</label>
            <select name="beat" id="beatSelect" disabled>
                <option value="">All Beats</option>
            </select>
        </div>

        <div class="filter-block">
            <label>Compartment</label>
            <select name="geofence" id="geofenceSelect" disabled>
                <option value="">All Compartments</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-apply">Apply</button>
            <a href="{{ url()->current() }}" class="btn-reset">Reset</a>
        </div>

    </form>
</div>
@endif
