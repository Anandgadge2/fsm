<!DOCTYPE html>
<html>
<head>
    <title>Guard Analytics</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        /* ===============================
           BASE LAYOUT
        ================================ */
        body {
            background: #f6f8f7; /* solid base – kills checker artifacts */
            overflow-x: hidden;
        }

        .sidebar {
            width:260px;
            position:fixed;
            top:0; left:0; bottom:0;
            background:#2f4f3f;
            color:#fff;
            z-index: 20;
        }

        .sidebar a {
            color:#dfe7df;
            display:block;
            padding:10px 20px;
            text-decoration:none;
        }

        .sidebar a:hover {
            background:#3e6b54;
        }

        .content {
            margin-left:260px;
            padding:20px;
            position: relative;
            z-index: 10;
        }

        /* ===============================
           GLOBAL FILTER BAR (unchanged)
        ================================ */
     .global-filter-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 18px 20px;
    margin-bottom: 18px;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);
}

.global-filter-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
    align-items: end;
}

.filter-block label {
    font-size: 12px;
    font-weight: 600;
    color: #566b56;
    margin-bottom: 6px;
}

.filter-block select,
.filter-block input {
    height: 40px;
    border-radius: 10px;
    border: 1px solid #d6e2d6;
    padding: 0 12px;
    font-size: 13px;
    background: #f8faf8;
}

.filter-block select:disabled {
    background: #f1f1f1;
    cursor: not-allowed;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.btn-apply {
    background: #2f4f3f;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 600;
}

.btn-apply:hover {
    background: #3f6b55;
}

.btn-reset {
    padding: 10px 12px;
    font-size: 13px;
    color: #6b7b6b;
    text-decoration: none;
}


 /* ===============================
   GLOBAL NATURE BACKGROUND (FIXED)
================================ */

/* Base layer */
.nature-bg {
    position: fixed;
    top: 0;
    bottom: 0;
    width: 420px;                 /* ⬆ bigger visual canvas */
    pointer-events: none;
    opacity: 0.5;                /* slightly more visible */
    background-repeat: no-repeat;
    background-size: contain;
    z-index: 0;                   /* ⬇ guaranteed behind content */
}

/* LEFT SIDE – bushes + large tree */
.left-bg1 {
    position: absolute;
    left: 240px;              /* after sidebar */
    bottom: 0;

    width: calc(100% - 240px);
    height: 140vh;             /* adjust based on how much bottom you want */

    background-image: url('/images/1.png');
    background-repeat: no-repeat;
    background-position: bottom center;
    background-size: cover;

    pointer-events: none;     /* optional: avoids blocking clicks */
    z-index: 0;               /* keep it behind cards */
}




/* RIGHT SIDE – paw trail (top) + big tree (bottom) */
.right-bg{
    right: 0;
    background-image:
        url('/images/3.png');
    background-position:
        top 10px right 40px,     /* paws clearly visible */
        bottom right;
    background-size:
        140%,      /* ⬆ paws much larger */
}


/* Soft vignette to blend edges */
body::after {
    content: "";
    position: fixed;
    inset: 0;
    background: radial-gradient(
        ellipse at center,
        rgba(255,255,255,0) 55%,
        rgba(246,248,247,0.85) 100%
    );
    pointer-events: none;
    z-index: 1;
}

/* Content always above backgrounds */
.container,
.container-fluid,
.content,
.dashboard-grid {
    position: relative;
    z-index: 5;
}

    </style>
</head>
<body>

@include('layouts.sidebar')

{{-- Decorative Backgrounds --}}
<div class="nature-bg left-bg1"></div>

<div class="nature-bg left-bg2"></div>
<div class="nature-bg right-bg"></div>


<div class="content">
    @include('partials.global-filters')
    @yield('content')
</div>

<script src="{{ asset('js/analytics.js') }}"></script>
</body>
</html>
