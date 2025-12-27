document.addEventListener('DOMContentLoaded', () => {

    const range = document.getElementById('rangeSelect');
    const beat = document.getElementById('beatSelect');
    const compartment = document.getElementById('compartmentSelect');

    if (!range || !beat || !compartment) return;

    function reset(select, label) {
        select.innerHTML = `<option value="">All ${label}</option>`;
        select.disabled = true;
    }

    function loadBeats(rangeId, selectedBeat = null) {
        reset(beat, 'Beats');
        reset(compartment, 'Compartments');
        if (!rangeId) return;

        fetch(`/filters/beats/${rangeId}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(b => {
                    beat.insertAdjacentHTML(
                        'beforeend',
                        `<option value="${b.id}" ${b.id == selectedBeat ? 'selected' : ''}>${b.name}</option>`
                    );
                });
                beat.disabled = false;
            });
    }

    function loadCompartments(beatId, selectedComp = null) {
        reset(compartment, 'Compartments');
        if (!beatId) return;

        fetch(`/filters/compartments/${beatId}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(c => {
                    compartment.insertAdjacentHTML(
                        'beforeend',
                        `<option value="${c.id}" ${c.id == selectedComp ? 'selected' : ''}>${c.name}</option>`
                    );
                });
                compartment.disabled = false;
            });
    }

    const params = new URLSearchParams(window.location.search);
    const r = params.get('range');
    const b = params.get('beat');
    const c = params.get('compartment');

    if (r) loadBeats(r, b);
    if (b) loadCompartments(b, c);

    range.addEventListener('change', () => loadBeats(range.value));
    beat.addEventListener('change', () => loadCompartments(beat.value));
});
