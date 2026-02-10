function initHospitalCharts() {
    // Get data passed from PHP
    const data = window.hospitalData || {
        requests: {
            open: 0,
            fulfilled: 0
        },
        urgency: {
            Critical: 0,
            High: 0,
            Medium: 0,
            Normal: 0
        }
    };

    // Global Chart Defaults
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = 'rgba(0,0,0,0.5)';

    // Cleanup existing charts if they exist
    ['requestStatusChart', 'urgencyChart'].forEach(id => {
        const canvas = document.getElementById(id);
        if (canvas) {
            const chart = Chart.getChart(canvas);
            if (chart) chart.destroy();
        }
    });

    // 1. Request Status Chart (Doughnut)
    const statusCtx = document.getElementById('requestStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Fulfilled'],
                datasets: [{
                    data: [data.requests.open, data.requests.fulfilled],
                    backgroundColor: [
                        '#fbb03b', // Pending (Orange/Yellow)
                        '#d4145a'  // Fulfilled (Pink/Red)
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { weight: '600' }
                        }
                    }
                },
                cutout: '75%'
            }
        });
    }

    // 2. Urgency Distribution Chart (Bar)
    const urgencyCtx = document.getElementById('urgencyChart');
    if (urgencyCtx) {
        new Chart(urgencyCtx, {
            type: 'bar',
            data: {
                labels: ['Critical', 'High', 'Medium', 'Normal'],
                datasets: [{
                    label: 'Requests',
                    data: [data.urgency.Critical, data.urgency.High, data.urgency.Medium, data.urgency.Normal],
                    backgroundColor: [
                        '#dc2626', // Critical
                        '#f59e0b', // High
                        '#3b82f6', // Medium
                        '#10b981'  // Normal
                    ],
                    borderRadius: 10,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initHospitalCharts);
window.initHospitalCharts = initHospitalCharts;
