const heatPoints = window.heatData || [];

const heat = L.heatLayer(heatPoints, {
    radius: 25,
    blur: 18,
    maxZoom: 17,
}).addTo(map);
