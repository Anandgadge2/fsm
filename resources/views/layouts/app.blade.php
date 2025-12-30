<!DOCTYPE html>
<html>
<head>
    <title>Guard Analytics</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/patrol-map.css') }}">

    <link rel="stylesheet" href="{{ asset('css/global-filters.css') }}">
    <link rel="stylesheet" href="{{ asset('css/table-sort.css') }}">
    <link rel="stylesheet" href="{{ asset('css/enhanced-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/global-filters.js') }}" defer></script>
    <script src="{{ asset('js/enhanced-table-sort.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
@include('layouts.sidebar')

{{-- Decorative Backgrounds --}}
<div class="nature-bg left-bg"></div>
<div class="nature-bg right-bg"></div>

<div class="content">
    @include('partials.global-filters')
    @yield('content')
</div>

{{-- Guard Detail Modal --}}
@include('partials.guard-detail-modal')

@stack('scripts')

</body>
</html>
