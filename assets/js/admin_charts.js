document.addEventListener('DOMContentLoaded', function() {
    const data = window.dashboardData || {
        users: { Donor: 0, HospitalAdmin: 0, SuperAdmin: 0 },
        hospitals: { verified: 0, pending: 0 },
        donations: { history: [] }
    };

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#6c757d';
    
    const userCtx = document.getElementById('userDistributionChart');
    if (userCtx) {
        new Chart(userCtx, {
            type: 'doughnut',
            data: {
                labels: ['Donors', 'Hospital Admins', 'Super Admins'],
                datasets: [{
                    data: [data.users.Donor, data.users.HospitalAdmin, data.users.SuperAdmin],
                    backgroundColor: [
                        '#6f42c1',
                        '#007bff',
                        '#28a745'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
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
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#2c3e50',
                        bodyColor: '#2c3e50',
                        borderColor: '#e9ecef',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true
                    }
                },
                cutout: '70%'
            }
        });
    }

    const donationCtx = document.getElementById('donationActivityChart');
    if (donationCtx) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const mockData = [12, 19, 15, 25, 22, 30]; 

        new Chart(donationCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Donations',
                    data: mockData,
                    borderColor: '#ff512f',
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(255, 81, 47, 0.2)');
                        gradient.addColorStop(1, 'rgba(255, 81, 47, 0)');
                        return gradient;
                    },
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ff512f',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#2c3e50',
                        bodyColor: '#2c3e50',
                        borderColor: '#e9ecef',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        grid: { color: '#f4f6f9', drawBorder: false },
                        beginAtZero: true
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    const hospitalCtx = document.getElementById('hospitalStatusChart');
    if (hospitalCtx) {
        new Chart(hospitalCtx, {
            type: 'bar',
            data: {
                labels: ['Verified', 'Pending'],
                datasets: [{
                    label: 'Hospitals',
                    data: [data.hospitals.verified, data.hospitals.pending],
                    backgroundColor: [
                        '#38ef7d',
                        '#f09819'
                    ],
                    borderRadius: 6,
                    barThickness: 40
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
                        grid: { color: '#f4f6f9', drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
