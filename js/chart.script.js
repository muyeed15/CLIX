// Chart colors
var colors = ['#007bff', '#28a745', '#333333', '#c3e6cb', '#dc3545', '#6c757d'];

// Large line chart
var chLine = document.getElementById("chLine");
if (chLine) {
    new Chart(chLine, {
        type: 'line',
        data: {
            labels: ["S", "M", "T", "W", "T", "F", "S"],
            datasets:
                [
                    {
                        data: [20, 42, 40, 57, 41, 100, 90],
                        backgroundColor: 'transparent',
                        borderColor: colors[4],
                        borderWidth: 4,
                        pointBackgroundColor: colors[4]
                    },
                    {
                        data: [30, 56, 80, 91, 82, 60, 55],
                        backgroundColor: 'transparent',
                        borderColor: colors[0],
                        borderWidth: 4,
                        pointBackgroundColor: colors[0]
                    },
                    {
                        data: [53, 18, 66, 76, 59, 89, 40],
                        backgroundColor: 'transparent',
                        borderColor: colors[1],
                        borderWidth: 4,
                        pointBackgroundColor: colors[1]
                    }
                ]
        },
        options: {
            scales: { xAxes: [{ ticks: { beginAtZero: false } }] },
            legend: { display: false },
            responsive: true
        }
    });
}

// Large bar chart
var chBar = document.getElementById("chBar");
if (chBar) {
    new Chart(chBar, {
        type: 'bar',
        data: {
            labels: ["S", "M", "T", "W", "T", "F", "S"],
            datasets: [
                { data: [589, 445, 483, 503, 689, 692, 634], backgroundColor: colors[0] },
                { data: [639, 465, 493, 478, 589, 632, 674], backgroundColor: colors[1] }
            ]
        },
        options: {
            legend: { display: false },
            scales: { xAxes: [{ barPercentage: 0.4, categoryPercentage: 0.5 }] }
        }
    });
}

// Donut chart options
var donutOptions = {
    cutoutPercentage: 85,
    legend: { position: 'bottom', padding: 5, labels: { pointStyle: 'circle', usePointStyle: true } }
};

// Donut 1
var chDonut1 = document.getElementById("chDonut1");
if (chDonut1) {
    new Chart(chDonut1, {
        type: 'pie',
        data: {
            labels: ['Gas', 'Water', 'Electricity'],
            datasets: [
                {
                    backgroundColor: ['#db3546', '#046ee5', '#29a645'],
                    borderWidth: 0,
                    data: [74, 11, 40]
                }
            ]
        },
        options: donutOptions
    });
}

// Donut 2
var chDonut2 = document.getElementById("chDonut2");
if (chDonut2) {
    new Chart(chDonut2, {
        type: 'pie',
        data: {
            labels: ['Wips', 'Pops', 'Dags'],
            datasets: [{ backgroundColor: colors.slice(0, 3), borderWidth: 0, data: [40, 45, 30] }]
        },
        options: donutOptions
    });
}

// Donut 3
var chDonut3 = document.getElementById("chDonut3");
if (chDonut3) {
    new Chart(chDonut3, {
        type: 'pie',
        data: {
            labels: ['Angular', 'React', 'Other'],
            datasets: [{ backgroundColor: colors.slice(0, 3), borderWidth: 0, data: [21, 45, 55] }]
        },
        options: donutOptions
    });
}

// Line chart options
var lineOptions = {
    legend: { display: false },
    scales: {
        xAxes: [{ ticks: { display: false }, gridLines: { display: false, drawBorder: false } }],
        yAxes: [{ display: false }]
    },
    layout: { padding: { left: 6, right: 6, top: 4, bottom: 6 } }
};

// Small line chart 1
var chLine1 = document.getElementById("chLine1");
if (chLine1) {
    new Chart(chLine1, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{ backgroundColor: '#ffffff', borderColor: '#ffffff', data: [10, 11, 4, 11, 4], fill: false }]
        },
        options: lineOptions
    });
}

// Small line chart 2
var chLine2 = document.getElementById("chLine2");
if (chLine2) {
    new Chart(chLine2, {
        type: 'line',
        data: {
            labels: ['A', 'B', 'C', 'D', 'E'],
            datasets: [{ backgroundColor: '#ffffff', borderColor: '#ffffff', data: [4, 5, 7, 13, 12], fill: false }]
        },
        options: lineOptions
    });
}

// Small line chart 3
var chLine3 = document.getElementById("chLine3");
if (chLine3) {
    new Chart(chLine3, {
        type: 'line',
        data: {
            labels: ['Pos', 'Neg', 'Nue', 'Other', 'Unknown'],
            datasets: [{ backgroundColor: '#ffffff', borderColor: '#ffffff', data: [13, 15, 10, 9, 14], fill: false }]
        },
        options: lineOptions
    });
}