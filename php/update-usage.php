<?php
global $conn;
session_start();
require_once './db-connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['_user_id_'])) {
    header("Location: access-denied.php");
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['_user_id_'];
$iot_id = $data['iot_id'];
$usage_amount = $data['usage_amount'];
$utility_type = $data['utility_type'];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // 1. Insert new usage record
    $usageQuery = "INSERT INTO usage_table (_user_id_, _iot_id_, _usage_time_, _usage_amount_) 
                   VALUES (?, ?, NOW(), ?)";
    $stmt = mysqli_prepare($conn, $usageQuery);
    mysqli_stmt_bind_param($stmt, "iid", $user_id, $iot_id, $usage_amount);
    mysqli_stmt_execute($stmt);

    // 2. Get utility cost per unit
    $costQuery = "SELECT ut._cost_per_unit_ 
                 FROM utility_table ut 
                 JOIN iot_table i ON ut._utility_id_ = i._utility_id_
                 WHERE i._iot_id_ = ?";
    $stmt = mysqli_prepare($conn, $costQuery);
    mysqli_stmt_bind_param($stmt, "i", $iot_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $costRow = mysqli_fetch_assoc($result);
    $cost_per_unit = $costRow['_cost_per_unit_'];

    // 3. Calculate cost and update balance
    $usage_cost = $usage_amount * $cost_per_unit;
    $balanceQuery = "UPDATE balance_table 
                     SET _current_balance_ = _current_balance_ - ? 
                     WHERE _user_id_ = ? AND _iot_id_ = ?";
    $stmt = mysqli_prepare($conn, $balanceQuery);
    mysqli_stmt_bind_param($stmt, "dii", $usage_cost, $user_id, $iot_id);
    mysqli_stmt_execute($stmt);

    // 4. Check if balance is now negative and update status if needed
    $checkBalanceQuery = "SELECT _current_balance_ FROM balance_table 
                         WHERE _user_id_ = ? AND _iot_id_ = ?";
    $stmt = mysqli_prepare($conn, $checkBalanceQuery);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $iot_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $balanceRow = mysqli_fetch_assoc($result);

    if ($balanceRow['_current_balance_'] <= 0) {
        // Move to unpaid status
        $unpaidQuery = "INSERT INTO unpaid_iot_table (_unpaid_iot_id_) VALUES (?)";
        $stmt = mysqli_prepare($conn, $unpaidQuery);
        mysqli_stmt_bind_param($stmt, "i", $iot_id);
        mysqli_stmt_execute($stmt);

        // Remove from active status if present
        $removeActiveQuery = "DELETE FROM active_iot_table WHERE _active_iot_id_ = ?";
        $stmt = mysqli_prepare($conn, $removeActiveQuery);
        mysqli_stmt_bind_param($stmt, "i", $iot_id);
        mysqli_stmt_execute($stmt);
    }

    // Commit transaction
    mysqli_commit($conn);

    // Get updated data for response
    $dataQuery = "SELECT b._current_balance_,
                        COALESCE(SUM(u._usage_amount_), 0) as total_usage,
                        CASE 
                            WHEN b._current_balance_ <= 0 OR up._unpaid_iot_id_ IS NOT NULL THEN 'Unpaid'
                            WHEN a._active_iot_id_ IS NOT NULL THEN 'Active'
                            WHEN ia._inactive_iot_id_ IS NOT NULL THEN 'Inactive'
                            ELSE 'Unknown'
                        END as status
                 FROM balance_table b
                 LEFT JOIN usage_table u ON b._iot_id_ = u._iot_id_
                 LEFT JOIN active_iot_table a ON b._iot_id_ = a._active_iot_id_
                 LEFT JOIN inactive_iot_table ia ON b._iot_id_ = ia._inactive_iot_id_
                 LEFT JOIN unpaid_iot_table up ON b._iot_id_ = up._unpaid_iot_id_
                 WHERE b._user_id_ = ? AND b._iot_id_ = ?
                 GROUP BY b._iot_id_";

    $stmt = mysqli_prepare($conn, $dataQuery);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $iot_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $updatedData = mysqli_fetch_assoc($result);

    echo json_encode([
        'success' => true,
        'data' => [
            'current_usage' => $usage_amount,
            'total_usage' => $updatedData['total_usage'],
            'balance' => $updatedData['_current_balance_'],
            'status' => $updatedData['status']
        ]
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['error' => $e->getMessage()]);
}

mysqli_close($conn);
