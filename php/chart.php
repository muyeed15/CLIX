<?php
global $conn;
if (!isset($_SESSION['_user_id_'])) {
    header("Location: access-denied.php");
    exit;
}
?>

<?php
$userId = $_SESSION['_user_id_'];

// Chart 1 (Last 7 Days)
$consumptionQuery = "SELECT 
                DATE(u._usage_time_) as date,
                SUM(CASE 
                    WHEN g._gas_id_ IS NOT NULL THEN u._usage_amount_
                    ELSE 0 
                END) as gas_consumption,
                SUM(CASE 
                    WHEN w._water_id_ IS NOT NULL THEN u._usage_amount_
                    ELSE 0 
                END) as water_consumption,
                SUM(CASE 
                    WHEN e._electricity_id_ IS NOT NULL THEN u._usage_amount_
                    ELSE 0 
                END) as electricity_consumption
            FROM usage_table u
            JOIN iot_table i ON u._iot_id_ = i._iot_id_
            JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
            LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
            LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
            LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
            WHERE u._user_id_ = ?
            GROUP BY DATE(u._usage_time_)
            ORDER BY date;
";

$consumptionStmt = $conn->prepare($consumptionQuery);
$consumptionStmt->bind_param("i", $userId);
$consumptionStmt->execute();
$consumptionResult = $consumptionStmt->get_result();

$consumptionData = [
    'dates' => [],
    'gas' => [],
    'water' => [],
    'electricity' => []
];

while ($row = $consumptionResult->fetch_assoc()) {
    $consumptionData['dates'][] = $row['date'];
    $consumptionData['gas'][] = (float)$row['gas_consumption'];
    $consumptionData['water'][] = (float)$row['water_consumption'];
    $consumptionData['electricity'][] = (float)$row['electricity_consumption'];
}

// Chart 2 (All time)
$totalUsageQuery = "SELECT 
                SUM(CASE 
                    WHEN g._gas_id_ IS NOT NULL THEN u._usage_amount_ 
                    ELSE 0 
                END) AS total_gas_usage,
                SUM(CASE 
                    WHEN w._water_id_ IS NOT NULL THEN u._usage_amount_ 
                    ELSE 0 
                END) AS total_water_usage,
                SUM(CASE 
                    WHEN e._electricity_id_ IS NOT NULL THEN u._usage_amount_ 
                    ELSE 0 
                END) AS total_electricity_usage  
            FROM usage_table u
            JOIN iot_table i ON u._iot_id_ = i._iot_id_
            JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
            LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
            LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
            LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
            WHERE u._user_id_ = ?;
";

$totalUsageStmt = $conn->prepare($totalUsageQuery);
$totalUsageStmt->bind_param("i", $userId);
$totalUsageStmt->execute();
$totalUsageResult = $totalUsageStmt->get_result();
$totalUsageData = $totalUsageResult->fetch_assoc();

$totalUsage = [
    'gas' => (int)$totalUsageData['total_gas_usage'],
    'water' => (int)$totalUsageData['total_water_usage'],
    'electricity' => (int)$totalUsageData['total_electricity_usage']
];
?>

<script>
    // Convert PHP arrays to JavaScript
    var consumptionData = {
        dates: <?php echo json_encode($consumptionData['dates']); ?>,
        gas: <?php echo json_encode($consumptionData['gas']); ?>,
        water: <?php echo json_encode($consumptionData['water']); ?>,
        electricity: <?php echo json_encode($consumptionData['electricity']); ?>
    };

    var totalUsageData = {
        gas: <?php echo $totalUsage['gas']; ?>,
        water: <?php echo $totalUsage['water']; ?>,
        electricity: <?php echo $totalUsage['electricity']; ?>
    };

    var colors = ['#3d81ff', '#ff5959', '#41f0ca'];

    // Line Chart
    var chLine = document.getElementById("chLine");
    if (chLine) {
        new Chart(chLine, {
            type: 'line',
            data: {
                labels: consumptionData.dates,
                datasets: [
                    {
                        label: "Gas (in cubic meters)",
                        data: consumptionData.gas,
                        backgroundColor: 'transparent',
                        borderColor: colors[1],
                        borderWidth: 4,
                        pointBackgroundColor: colors[1]
                    },
                    {
                        label: "Water (in liters)",
                        data: consumptionData.water,
                        backgroundColor: 'transparent',
                        borderColor: colors[0],
                        borderWidth: 4,
                        pointBackgroundColor: colors[0]
                    },
                    {
                        label: "Electricity (in kWh)",
                        data: consumptionData.electricity,
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

    // Donut Chart
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
                            totalUsageData.gas,
                            totalUsageData.water,
                            totalUsageData.electricity
                        ]
                    }
                ]
            },
            options: {
                cutoutPercentage: 85,
                legend: {position: 'bottom', padding: 5, labels: {pointStyle: 'circle', usePointStyle: true}}
            }
        });
    }
</script>
