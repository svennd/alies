
/*
    WBC Chart
*/

const wbc_labels = wbc_data.map((_, i) => i + 1);
const ctx = document.getElementById('my-wbc').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: wbc_labels, // X-axis labels
        datasets: [{
            label: 'WBC Count',
            data: wbc_data, // Y-axis data
            backgroundColor: 'rgba(246, 198, 38, 0.2)', // Fill color
            borderColor: 'rgba(246, 198, 38, 1)', // Line color
            borderWidth: 1,
            fill: true, // Enable filling
            pointRadius: 0, // Hide the points
            // tension: 0.1 // smooth the curve a bit
        }]
    },
    options: {
        animation: false, // Disable animations
        responsive: true,
        maintainAspectRatio: false, // Allow size adjustment
        scales: {
            x: {
                ticks: {
                    callback: function(value, index, values) {
                        // Show label only if index is divisible by 10
                        return index % 10 === 0 ? value : '';
                    }
                }
            },
            y: {
                title: {
                    display: false
                },
                ticks: {
                    // display: false,
                    stepSize: 10 // Show every 10 ticks
                },
                beginAtZero: true
            }
        }
    }
});
/*
    RBC Chart
*/

const rbc_labels = rbc_data.map((_, i) => i + 1);
const ctx_rbc = document.getElementById('my-rbc').getContext('2d');
const myChart_wbc = new Chart(ctx_rbc, {
    type: 'line',
    data: {
        labels: rbc_labels, // X-axis labels
        datasets: [{
            label: 'RBC Count',
            data: rbc_data, // Y-axis data
            backgroundColor: 'rgba(255, 99, 132, 0.2)', // Fill color
            borderColor: 'rgba(255, 99, 132, 1)', // Line color
            borderWidth: 1,
            fill: true, // Enable filling
            pointRadius: 0, // Hide the points
            // tension: 0.01 // smooth the curve a bit
        }]
    },
    options: {
        animation: false, // Disable animations
        responsive: true,
        maintainAspectRatio: false, // Allow size adjustment
        scales: {
            x: {
                ticks: {
                    callback: function(value, index, values) {
                        // Show label only if index is divisible by 10
                        return index % 10 === 0 ? value : '';
                    }
                }
            },
            y: {
                title: {
                    display: false
                },
                ticks: {
                    stepSize: 10 // Show every 10 ticks
                },
                beginAtZero: true
            }
        }
    }
});

/*
    PLT Chart
*/

const plt_labels = plt_data.map((_, i) => i + 1);
const ctx_plt = document.getElementById('my-plt').getContext('2d');
const myChart_plt = new Chart(ctx_plt, {
    type: 'line',
    data: {
        labels: plt_labels, // X-axis labels
        datasets: [{
            label: 'PLT Count',
            data: plt_data, // Y-axis data
            backgroundColor: 'rgba(0, 183, 135, 0.2)', // Fill color
            borderColor: 'rgba(0, 183, 135, 1)', // Line color
            borderWidth: 1,
            fill: true, // Enable filling
            pointRadius: 0, // Hide the points
            // tension:0.4 // smooth the curve a bit
        }]
    },
    options: {
        animation: false, // Disable animations
        responsive: true,
        maintainAspectRatio: false, // Allow size adjustment
        scales: {
            x: {

                ticks: {
                    callback: function(value, index, values) {
                        // Show label only if index is divisible by 10
                        return index % 10 === 0 ? value : '';
                    }
                }
                // grid: {
                //     display: true // Optionally, show or hide the grid lines
                // }
            },
            y: {
                title: {
                    display: false,
                },
                // ticks: {
                //     stepSize: 10 // Show every 10 ticks
                // },
                // beginAtZero: true
            }
        }
    }
});