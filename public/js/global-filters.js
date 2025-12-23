document.addEventListener("DOMContentLoaded", () => {

    const range = document.getElementById("rangeSelect");
    const beat = document.getElementById("beatSelect");
    const geo = document.getElementById("geofenceSelect");

    range.addEventListener("change", async () => {
        beat.innerHTML = `<option value="">All Beats</option>`;
        geo.innerHTML = `<option value="">All Compartments</option>`;
        geo.disabled = true;

        if (!range.value) {
            beat.disabled = true;
            return;
        }

        const res = await fetch(`/filters/beats?range=${range.value}`);
        const data = await res.json();

        data.forEach(b => {
            beat.innerHTML += `<option value="${b.name}">${b.name}</option>`;
        });

        beat.disabled = false;
    });

    beat.addEventListener("change", async () => {
        geo.innerHTML = `<option value="">All Compartments</option>`;

        if (!beat.value) {
            geo.disabled = true;
            return;
        }

        const res = await fetch(`/filters/geofences?beat=${beat.value}`);
        const data = await res.json();

        data.forEach(g => {
            geo.innerHTML += `<option value="${g.geo_name}">${g.geo_name}</option>`;
        });

        geo.disabled = false;
    });
});
