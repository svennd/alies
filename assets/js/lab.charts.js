function createLabChart(canvasId, label, data, borderColor, backgroundColor, markers = []) {

    if (!data || !document.getElementById(canvasId)) return;

    const labels = data.map((_, i) => i + 1);

    const annotations = {};

    markers.forEach((m, i) => {
        annotations['line' + i] = {
            type: 'line',
            xMin: m,
            xMax: m,
            borderColor: 'rgba(240, 151, 18, 0.49)',
            borderWidth: 1
        };
    });

    new Chart(document.getElementById(canvasId), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: backgroundColor,
                borderColor: borderColor,
                borderWidth: 1,
                fill: true,
                pointRadius: 0
            }]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                annotation: {
                    annotations: annotations
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value, index) {
                            return index % 10 === 0 ? value : '';
                        }
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

if (typeof labPlots !== 'undefined') {

	createLabChart(
		'chart-wbc',
		'WBC Count',
		labPlots.WBC,
		'rgba(246, 198, 38, 1)',
		'rgba(246, 198, 38, 0.2)',
		labMarkers
	);
	
    createLabChart(
        'chart-rbc',
        'RBC Count',
        labPlots.RBC,
        'rgba(255, 99, 132, 1)',
        'rgba(255, 99, 132, 0.2)'
    );

    createLabChart(
        'chart-thr',
        'PLT Count',
        labPlots.THR,
        'rgba(0, 183, 135, 1)',
        'rgba(0, 183, 135, 0.2)'
    );
}