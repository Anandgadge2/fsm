document.addEventListener("DOMContentLoaded", () => {

    function donut(id, labels, data) {
        const el = document.getElementById(id);
        if (!el) return;

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: [
                        '#8d6e63','#66bb6a','#42a5f5','#ef5350','#ab47bc','#ffa726'
                    ]
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }

    donut(
        'rangeDistanceChart',
        JSON.parse(document.getElementById('rangeDistanceChart').dataset.labels),
        JSON.parse(document.getElementById('rangeDistanceChart').dataset.values)
    );

    donut(
        'rangeOfficerChart',
        JSON.parse(document.getElementById('rangeOfficerChart').dataset.labels),
        JSON.parse(document.getElementById('rangeOfficerChart').dataset.values)
    );
});
