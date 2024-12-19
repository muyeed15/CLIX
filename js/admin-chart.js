// Color scheme
const colors = {
    water: '#007bff',
    electricity: '#28a745',
    gas: '#dc3545',
    active: '#28a745',
    inactive: '#dc3545',
    impact: ['#dc3545', '#ffc107', '#28a745']
};

// Initialize all charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // 1. Monthly Utility Usage Trends
    const monthlyTrendsChart = new Chart(document.getElementById('monthlyTrendsChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'Water Usage',
                data: monthlyData.map(d => d.water_usage),
                borderColor: colors.water,
                fill: false
            }, {
                label: 'Electricity Usage',
                data: monthlyData.map(d => d.electricity_usage),
                borderColor: colors.electricity,
                fill: false
            }, {
                label: 'Gas Usage',
                data: monthlyData.map(d => d.gas_usage),
                borderColor: colors.gas,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 2. IoT Device Status
    const iotStatusChart = new Chart(document.getElementById('iotStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [
                    iotData.reduce((sum, d) => sum + parseInt(d.active_devices), 0),
                    iotData.reduce((sum, d) => sum + parseInt(d.inactive_devices), 0)
                ],
                backgroundColor: [colors.active, colors.inactive]
            }]
        },
        options: {
            responsive: true
        }
    });

    // 3. User Consumption Pattern
    const consumptionPatternChart = new Chart(document.getElementById('consumptionPatternChart'), {
        type: 'line',
        data: {
            labels: userPatternData.map(d => d.hour_of_day),
            datasets: [{
                label: 'Average Usage',
                data: userPatternData.map(d => d.avg_usage),
                borderColor: colors.water,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 4. Outage Impact
    const outageImpactChart = new Chart(document.getElementById('outageImpactChart'), {
        type: 'pie',
        data: {
            labels: ['High Impact', 'Medium Impact', 'Low Impact'],
            datasets: [{
                data: [
                    outageData.high_impact,
                    outageData.medium_impact,
                    outageData.low_impact
                ],
                backgroundColor: colors.impact
            }]
        },
        options: {
            responsive: true
        }
    });

    // 5. User Activity
    const userActivityChart = new Chart(document.getElementById('userActivityChart'), {
        type: 'bar',
        data: {
            labels: activityData.map(d => d.date),
            datasets: [{
                label: 'Daily Logins',
                data: activityData.map(d => d.login_count),
                backgroundColor: colors.water
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 6. Feedback Analysis
    const feedbackChart = new Chart(document.getElementById('feedbackChart'), {
        type: 'bar',
        data: {
            labels: feedbackData.map(d => d.month),
            datasets: [{
                label: 'Feedback Count',
                data: feedbackData.map(d => d.feedback_count),
                backgroundColor: colors.electricity
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});