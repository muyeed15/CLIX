<?php
session_start();
require_once 'db-connection.php';

if (!isset($_SESSION['_user_id_'])) {
    header("Location: ./login.php");
    exit;
}

$nid = $_SESSION['_user_id_'];

// Fetch consumption data for the last 7 days
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

