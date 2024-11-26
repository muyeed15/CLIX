fetch('dashboard-usage.php')
    .then(response => response.json())
    .then(data => {
        var labels = data.consumptionData.dates;
        var dailyConsumptionData = {
            gas: data.consumptionData.gas,
            water: data.consumptionData.water,
            electricity: data.consumptionData.electricity
        };

        var colors = ['#3d81ff', '#ff5959', '#41f0ca'];

        // Line Chart
        var chLine = document.getElementById("chLine");
        if (chLine) {
            new Chart(chLine, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Gas (in cubic meters)",
                            data: dailyConsumptionData.gas,
                            backgroundColor: 'transparent',
                            borderColor: colors[1],
                            borderWidth: 4,
                            pointBackgroundColor: colors[1]
                        },
                        {
                            label: "Water (in liters)",
                            data: dailyConsumptionData.water,
                            backgroundColor: 'transparent',
                            borderColor: colors[0],
                            borderWidth: 4,
                            pointBackgroundColor: colors[0]
                        },
                        {
                            label: "Electricity (in kWh)",
                            data: dailyConsumptionData.electricity,
                            backgroundColor: 'transparent',
                            borderColor: colors[2],
                            borderWidth: 4,
                            pointBackgroundColor: colors[2]
                        }
                    ]
                },
                options: {
                    scales: {
                        x: {
                            beginAtZero: false
                        }
                    },
                    legend: {
                        display: true
                    },
                    responsive: true
                }
            });
        }

        // Donut Chart 1
        var chDonut1 = document.getElementById("chDonut1");
        if (chDonut1) {
            new Chart(chDonut1, {
                type: 'pie',
                data: {
                    labels: ['Gas', 'Water', 'Electricity'],
                    datasets: [
                        {
                            backgroundColor: [colors[1], colors[0], colors[2]],
                            borderWidth: 0,
                            data: [
                                data.totalUsageData.gas,
                                data.totalUsageData.water,
                                data.totalUsageData.electricity
                            ]
                        }
                    ]
                },
                options: {
                    cutoutPercentage: 85,
                    legend: { position: 'bottom', padding: 5, labels: { pointStyle: 'circle', usePointStyle: true } }
                }
            });
        }
    })
    .catch(error => console.error('Error fetching usage data:', error));
