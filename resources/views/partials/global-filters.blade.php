@if (!isset($hideFilters) || !$hideFilters)

<div class="global-filter-card sticky-filter">
    <form method="GET" class="global-filter-grid">

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

        <div class="filter-block">
            <label>Beat</label>
            <select name="beat" id="beatSelect">
                <option value="">All Beats</option>
                @foreach($beats as $id => $name)
                    <option value="{{ $id }}" {{ request('beat') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-block">
            <label>Compartment</label>
            <select name="compartment" id="compartmentSelect">
                <option value="">All Compartments</option>
                @foreach($compartments as $id => $name)
                    <option value="{{ $id }}" {{ request('compartment') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-block">
            <label>Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>

        <div class="filter-block">
            <label>End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-apply">Apply</button>
            <a href="{{ url()->current() }}" class="btn-reset">Reset</a>
        </div>

    </form>
</div>

@endif
