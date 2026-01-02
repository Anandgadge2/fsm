@if (!isset($hideFilters) || !$hideFilters)

<div class="global-filter-card sticky-filter">
    <form method="GET" class="global-filter-grid">

        {{-- Keep other query params (pagination, sorting, etc.) --}}
        @foreach(request()->except(['range','beat','user','start_date','end_date']) as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach

        {{-- RANGE --}}
        <div class="filter-block">
            <label>Range</label>
            <select name="range" id="rangeSelect" onchange="this.form.submit()">
                <option value="">All Ranges</option>
                @foreach($ranges as $id => $name)
                    <option value="{{ $id }}" {{ request('range') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- BEAT (Dependent on Range) --}}
        <div class="filter-block">
            <label>Beat</label>
            <select name="beat" id="beatSelect" onchange="this.form.submit()">
                <option value="">All Beats</option>
                @foreach($beats as $id => $name)
                    <option value="{{ $id }}" {{ request('beat') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- USER (Dependent on Beat/Range) --}}
        <div class="filter-block">
            <label>Guard / User</label>
            <select name="user" id="userSelect" onchange="this.form.submit()">
                <option value="">All Guards</option>
                @foreach($users as $id => $name)
                    <option value="{{ $id }}" {{ request('user') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- DATE --}}
        <div class="filter-block">
            <label>Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()">
        </div>

        <div class="filter-block">
            <label>End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()">
        </div>
        
        {{-- Loader (Hidden by default, can be toggled by JS on submit if desired) --}}
        <div class="filter-loading">
            <div class="spinner"></div> Loading...
        </div>

    </form>
</div>

{{-- Add some minor script to show loader on submit --}}
<script>
    document.querySelectorAll('.global-filter-grid select, .global-filter-grid input').forEach(el => {
        el.addEventListener('change', () => {
            const loader = document.querySelector('.filter-loading');
            if(loader) loader.style.display = 'flex';
        });
    });
</script>

@endif
