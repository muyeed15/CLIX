<?php
session_start();
require_once 'db-connection.php';

if (!isset($_SESSION['nid'])) {
    header("Location: login.php");
    exit;
}

$nid = $_SESSION['nid'];

// Fetch consumption data for the last 7 days
$consumptionQuery = "
    SELECT
        DATE_FORMAT(u._date_, '%Y-%m-%d') AS date,
        SUM(CASE WHEN i._type_ = 'Gas' THEN u._usage_amount_ ELSE 0 END) AS gas_consumption,
        SUM(CASE WHEN i._type_ = 'Water' THEN u._usage_amount_ ELSE 0 END) AS water_consumption,
        SUM(CASE WHEN i._type_ = 'Electricity' THEN u._usage_amount_ ELSE 0 END) AS electricity_consumption
    FROM usage_t u
    JOIN iot_utility_t i ON u._iot_id_ = i._iot_id_
    WHERE u._nid_ = ?
    GROUP BY u._date_
    ORDER BY u._date_ ASC
";

// u._date_ >= CURDATE() - INTERVAL 7 DAY AND 

$consumptionStmt = $conn->prepare($consumptionQuery);
$consumptionStmt->bind_param("i", $nid);
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

// Fetch total usage data
$totalUsageQuery = "
    SELECT
        SUM(CASE WHEN i._type_ = 'Gas' THEN u._usage_amount_ ELSE 0 END) AS total_gas_usage,
        SUM(CASE WHEN i._type_ = 'Water' THEN u._usage_amount_ ELSE 0 END) AS total_water_usage,
        SUM(CASE WHEN i._type_ = 'Electricity' THEN u._usage_amount_ ELSE 0 END) AS total_electricity_usage
    FROM usage_t u
    JOIN iot_utility_t i ON u._iot_id_ = i._iot_id_
    WHERE u._nid_ = ?;
";

$totalUsageStmt = $conn->prepare($totalUsageQuery);
$totalUsageStmt->bind_param("i", $nid);
$totalUsageStmt->execute();
$totalUsageResult = $totalUsageStmt->get_result();
$totalUsageData = $totalUsageResult->fetch_assoc();

// Prepare total usage data
$totalUsage = [
    'gas' => (int)$totalUsageData['total_gas_usage'],
    'water' => (int)$totalUsageData['total_water_usage'],
    'electricity' => (int)$totalUsageData['total_electricity_usage']
];

// Combined response
$response = [
    'consumptionData' => $consumptionData,
    'totalUsageData' => $totalUsage
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();

