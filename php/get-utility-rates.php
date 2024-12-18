<?php
header('Content-Type: application/json');
require_once 'db-connection.php';

try {
    $query = "
        SELECT 
            u._utility_id_,
            u._cost_per_unit_,
            CASE
                WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                WHEN w._water_id_ IS NOT NULL THEN 'Water'
                WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
            END as utility_type
        FROM utility_table u
        LEFT JOIN gas_table g ON u._utility_id_ = g._gas_id_
        LEFT JOIN water_table w ON u._utility_id_ = w._water_id_
        LEFT JOIN electricity_table e ON u._utility_id_ = e._electricity_id_
        WHERE g._gas_id_ IS NOT NULL 
           OR w._water_id_ IS NOT NULL 
           OR e._electricity_id_ IS NOT NULL
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }

    $rates = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rates[] = array(
            '_utility_id_' => (int)$row['_utility_id_'],
            '_cost_per_unit_' => (float)$row['_cost_per_unit_'],
            'utility_type' => $row['utility_type']
        );
    }

    $foundTypes = array_column($rates, 'utility_type');
    $requiredTypes = ['Gas', 'Water', 'Electricity'];
    $missingTypes = array_diff($requiredTypes, $foundTypes);

    if (!empty($missingTypes)) {
        throw new Exception('Missing rates for utilities: ' . implode(', ', $missingTypes));
    }

    $response = array(
        'success' => true,
        'rates' => $rates
    );

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log('Error in get-utility-rates.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to retrieve utility rates',
        'debug_message' => $e->getMessage()
    ));
}

mysqli_close($conn);