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
        html, body {
    height: 100%;
    overflow: hidden; /* 🔑 stop page scrolling */
    background: #f6f8f7;
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
    margin-left: 260px;
    padding: 20px;
    height: 100vh;
    overflow-y: auto;   /* ✅ ONLY CONTENT SCROLLS */
    overflow-x: hidden;
    position: relative;
    z-index: 10;
}
       /* ===============================
           GLOBAL FILTER BAR (unchanged)
        ================================ */
/* ===============================
   GLOBAL FILTER BAR (REFINED)
================================ */
.global-filter-wrapper {
    position: sticky;
    top: 0;
    z-index: 30;
}

.global-filter-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px 18px;
    margin-bottom: 18px;
    box-shadow: 0 10px 26px rgba(0,0,0,0.08);
    position: relative;
}

.global-filter-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(160px, 1fr));
    gap: 16px;
    align-items: end;
}

.filter-block {
    display: flex;
    flex-direction: column;
}

.filter-block select:disabled {
    background: #f1f3f1;
    cursor: not-allowed;
    opacity: 0.65;
}


.filter-block label {
    font-size: 11.5px;
    font-weight: 700;
    color: #516b55;
    margin-bottom: 6px;
}

.filter-block select,
.filter-block input {
    height: 38px;
    border-radius: 10px;
    border: 1px solid #d9e4d9;
    padding: 0 12px;
    font-size: 13px;
    background: #f8fbf8;
}

.filter-block select:focus,
.filter-block input:focus {
    outline: none;
    border-color: #4f6f52;
    background: #ffffff;
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-apply {
    background: #2f4f3f;
    color: #fff;
    border: none;
    padding: 9px 18px;
    border-radius: 10px;
    font-weight: 700;
}

.btn-apply:hover {
    background: #3f6b55;
}

.btn-reset {
    padding: 9px 12px;
    font-size: 12.5px;
    color: #6b7b6b;
    text-decoration: none;
}

/* Smooth entry */
.animate-slide {
    animation: slideDown .35s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.filter-loading {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.85);
    border-radius: 16px;
    display: none;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-weight: 700;
    color: #2f4f3f;
    z-index: 40;
}

.spinner {
    width: 22px;
    height: 22px;
    border: 3px solid #cfdacf;
    border-top: 3px solid #2f4f3f;
    border-radius: 50%;
    animation: spin .8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
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
    height: 120vh;             /* adjust based on how much bottom you want */
    background-image: url('/images/1.png');
    background-repeat: no-repeat;
    background-position: bottom center;
    background-size: cover;
    filter: brightness(0.5) contrast(1.2);
    pointer-events: none;     /* optional: avoids blocking clicks */
    z-index: 0;  
                 /* keep it behind cards */
}

.sticky-filter {
    position: sticky;
    top: 12px;
    z-index: 50;
}

.btn-loader {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* RIGHT SIDE – paw trail (top) + big tree (bottom) */
.right-bg {
    left: 100px;
    top:-290px;
    background-image: url('/images/3.png');
    background-position: top 0px right 40px;
    background-size: 120%;
    
    /* Add rotation and positioning */
    transform: rotate(60deg) ;
    
    /* Make image darker */
    filter: brightness(0.7) contrast(1.1);
    

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
@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const rangeSelect = document.getElementById('rangeSelect');
    const beatSelect = document.getElementById('beatSelect');
    const compartmentSelect = document.getElementById('compartmentSelect');
    const form = document.getElementById('globalFilterForm');

    function resetBeats() {
        beatSelect.innerHTML = `<option value="">All Beats</option>`;
        beatSelect.disabled = true;
    }

    function resetCompartments() {
        compartmentSelect.innerHTML = `<option value="">All Compartments</option>`;
        compartmentSelect.disabled = true;
    }

    /* ===============================
       RANGE → BEATS
    =============================== */
    rangeSelect.addEventListener('change', async () => {

        const rangeId = rangeSelect.value;

        resetBeats();
        resetCompartments();

        // 🚨 IMPORTANT: stop here for "All Ranges"
        if (!rangeId) {
            return;
        }

        beatSelect.disabled = true;
        beatSelect.innerHTML = `<option>Loading beats...</option>`;

        try {
            const res = await fetch(`/filters/beats/${rangeId}`);
            const data = await res.json();

            beatSelect.innerHTML = `<option value="">All Beats</option>`;

            data.forEach(b => {
                beatSelect.innerHTML += `<option value="${b.id}">${b.name}</option>`;
            });

            beatSelect.disabled = false;

        } catch (e) {
            beatSelect.innerHTML = `<option value="">All Beats</option>`;
            beatSelect.disabled = true;
            console.error(e);
        }
    });

    /* ===============================
       BEAT → COMPARTMENTS
    =============================== */
    beatSelect.addEventListener('change', async () => {

        const beatId = beatSelect.value;

        resetCompartments();

        // 🚨 stop if "All Beats"
        if (!beatId) {
            return;
        }

        compartmentSelect.disabled = true;
        compartmentSelect.innerHTML = `<option>Loading compartments...</option>`;

        try {
            const res = await fetch(`/filters/compartments/${beatId}`);
            const data = await res.json();

            compartmentSelect.innerHTML = `<option value="">All Compartments</option>`;

            data.forEach(c => {
                compartmentSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });

            compartmentSelect.disabled = false;

        } catch (e) {
            compartmentSelect.innerHTML = `<option value="">All Compartments</option>`;
            compartmentSelect.disabled = true;
            console.error(e);
        }
    });

});
</script>







</body>
</html>
