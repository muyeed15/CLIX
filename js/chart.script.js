var colors = ['#007bff', '#dc3545', '#28a745'];

var chLine = document.getElementById("chLine");
if (chLine) {
    new Chart(chLine, {
        type: 'line',
        data: {
            labels: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            datasets: [
                {
                    label: "Gas Consumption (in cubic meters)",
                    data: [20, 42, 40, 57, 41, 100, 90],
                    backgroundColor: 'transparent',
                    borderColor: colors[1],
                    borderWidth: 4,
                    pointBackgroundColor: colors[1]
                },
                {
                    label: "Water Consumption (in liters)",
                    data: [30, 56, 80, 91, 82, 60, 55],
                    backgroundColor: 'transparent',
                    borderColor: colors[0],
                    borderWidth: 4,
                    pointBackgroundColor: colors[0]
                },
                {
                    label: "Electricity Consumption (in kWh)",
                    data: [53, 18, 66, 76, 59, 89, 40],
                    backgroundColor: 'transparent',
                    borderColor: colors[2],
                    borderWidth: 4,
                    pointBackgroundColor: colors[2]
                }
            ]
        },
        options: {
            scales: {
                xAxes: [{
                    ticks: { beginAtZero: false }
                }]
            },
            legend: {
                display: true
            },
            responsive: true
        }
    });
}

var chBar = document.getElementById("chBar");
if (chBar) {
    new Chart(chBar, {
        type: 'bar',
        data: {
            labels: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            datasets: [
                {
                    data: [589, 445, 483, 503, 689, 692, 634],
                    backgroundColor: colors[0],
                    label: "Water Usage (liters)"
                },
                {
                    data: [639, 465, 493, 478, 589, 632, 674],
                    backgroundColor: colors[2],
                    label: "Electricity Usage (kWh)"
                },
                {
                    data: [120, 150, 170, 130, 110, 145, 160],
                    backgroundColor: colors[1],
                    label: "Gas Usage (cubic meters)"
                }
            ]
        },
        options: {
            legend: { display: true },
            scales: {
                xAxes: [{
                    barPercentage: 0.4,
                    categoryPercentage: 0.5
                }]
            }
        }
    });
}

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
                    data: [74, 11, 40]
                }
            ]
        },
        options: {
            cutoutPercentage: 85,
            legend: { position: 'bottom', padding: 5, labels: { pointStyle: 'circle', usePointStyle: true } }
        }
    });
}

var chDonut2 = document.getElementById("chDonut2");
if (chDonut2) {
    new Chart(chDonut2, {
        type: 'pie',
        data: {
            labels: ['Medium Energy User', 'High Energy User', 'Low Energy User'],
            datasets: [
                {
                    backgroundColor: colors.slice(0, 3),
                    borderWidth: 0,
                    data: [40, 45, 15]
                }
            ]
        },
        options: {
            cutoutPercentage: 85,
            legend: { position: 'bottom', padding: 5, labels: { pointStyle: 'circle', usePointStyle: true } }
        }
    });
}

var chDonut3 = document.getElementById("chDonut3");
if (chDonut3) {
    new Chart(chDonut3, {
        type: 'pie',
        data: {
            labels: ['iOS Users', 'Web Users', 'Android Users'],
            datasets: [
                {
                    backgroundColor: colors.slice(0, 3),
                    borderWidth: 0,
                    data: [10, 60, 30]
                }
            ]
        },
        options: {
            cutoutPercentage: 85,
            legend: { position: 'bottom', padding: 5, labels: { pointStyle: 'circle', usePointStyle: true } }
        }
    });
}

var chLine1 = document.getElementById("chLine1");
if (chLine1) {
    new Chart(chLine1, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'],
            datasets: [
                {
                    backgroundColor: '#ffffff',
                    borderColor: '#ffffff',
                    data: [120, 150, 130, 170, 200],
                    fill: false
                }
            ]
        },
        options: {
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { display: false }, gridLines: { display: false, drawBorder: false } }],
                yAxes: [{ display: false }]
            },
            layout: { padding: { left: 6, right: 6, top: 4, bottom: 6 } }
        }
    });
}

var chLine2 = document.getElementById("chLine2");
if (chLine2) {
    new Chart(chLine2, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'],
            datasets: [
                {
                    backgroundColor: '#ffffff',
                    borderColor: '#ffffff',
                    data: [150, 170, 160, 180, 200],
                    fill: false
                }
            ]
        },
        options: {
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { display: false }, gridLines: { display: false, drawBorder: false } }],
                yAxes: [{ display: false }]
            },
            layout: { padding: { left: 6, right: 6, top: 4, bottom: 6 } }
        }
    });
}

var chLine3 = document.getElementById("chLine3");
if (chLine3) {
    new Chart(chLine3, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'],
            datasets: [
                {
                    backgroundColor: '#ffffff',
                    borderColor: '#ffffff',
                    data: [300, 350, 400, 450, 500],
                    fill: false
                }
            ]
        },
        options: {
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { display: false }, gridLines: { display: false, drawBorder: false } }],
                yAxes: [{ display: false }]
            },
            layout: { padding: { left: 6, right: 6, top: 4, bottom: 6 } }
        }
    });
}