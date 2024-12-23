<?php
ob_start();
global $conn;
session_start();
require_once './db-connection.php';

// Add handlers for approve and reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Handle IoT creation
        if (isset($_POST['create_iot'])) {
            $prefix = match ($_POST['iot_type']) {
                'Electricity' => '1',
                'Water' => '2',
                'Gas' => '3'
            };
            $utilityId = intval($prefix);

            do {
                $iotId = $prefix . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
                $check = $conn->query("SELECT _iot_id_ FROM iot_table WHERE _iot_id_ = $iotId");
            } while ($check->num_rows > 0);

            $stmt = $conn->prepare("INSERT INTO iot_table (_iot_id_, _utility_id_, _iot_label_, _iot_latitude_, _iot_longitude_, _last_reported_time_) VALUES (?, ?, NULL, '0.000000', '0.000000', NOW())");
            $stmt->bind_param('ii', $iotId, $utilityId);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO inactive_iot_table (_inactive_iot_id_) VALUES (?)");
            $stmt->bind_param('i', $iotId);
            $stmt->execute();

            $_SESSION['success'] = "IoT device created successfully with ID: " . $iotId;
        }

        // Inside the POST handler for approve_request
        if (isset($_POST['approve_request'])) {
            // Validate input
            $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $iotId = filter_input(INPUT_POST, 'iot_id', FILTER_VALIDATE_INT);

            // Validate inputs
            if ($requestId === false || $userId === false || $iotId === false) {
                throw new Exception("Invalid request parameters");
            }

            // Verify request exists and get its location data
            $checkRequestStmt = $conn->prepare("SELECT * FROM request_table WHERE _request_id_ = ? AND _user_id_ = ? AND _iot_id_ = ?");
            $checkRequestStmt->bind_param('iii', $requestId, $userId, $iotId);
            $checkRequestStmt->execute();
            $requestResult = $checkRequestStmt->get_result();

            if ($requestResult->num_rows === 0) {
                throw new Exception("Request not found");
            }

            $requestData = $requestResult->fetch_assoc();
            $latitude = $requestData['_latitude_'];
            $longitude = $requestData['_longitude_'];

            // Check if IoT device is already active or in use
            $checkActiveStmt = $conn->prepare("SELECT * FROM active_iot_table WHERE _active_iot_id_ = ?");
            $checkActiveStmt->bind_param('i', $iotId);
            $checkActiveStmt->execute();
            $activeResult = $checkActiveStmt->get_result();

            if ($activeResult->num_rows > 0) {
                throw new Exception("IoT device is already active");
            }

            // Update IoT device location
            $updateLocationStmt = $conn->prepare("UPDATE iot_table SET _iot_latitude_ = ?, _iot_longitude_ = ? WHERE _iot_id_ = ?");
            $updateLocationStmt->bind_param('ddi', $latitude, $longitude, $iotId);
            $updateLocationStmt->execute();

            // Remove from pending_request_table if exists
            $stmt = $conn->prepare("DELETE FROM pending_request_table WHERE _pending_request_id_ = ?");
            $stmt->bind_param('i', $requestId);
            $stmt->execute();

            // Remove from request_table
            $stmt = $conn->prepare("DELETE FROM request_table WHERE _request_id_ = ?");
            $stmt->bind_param('i', $requestId);
            $stmt->execute();

            // Insert initial usage record with 0.00 usage amount
            $stmt = $conn->prepare("INSERT INTO usage_table (_user_id_, _iot_id_, _usage_time_, _usage_amount_) VALUES (?, ?, NOW(), 0.00)");
            $stmt->bind_param('ii', $userId, $iotId);
            $stmt->execute();

            // Insert initial balance record with 0.00 balance
            $stmt = $conn->prepare("INSERT INTO balance_table (_user_id_, _iot_id_, _current_balance_) VALUES (?, ?, 0.00)");
            $stmt->bind_param('ii', $userId, $iotId);
            $stmt->execute();

            // Update IoT device status to active
            $stmt = $conn->prepare("DELETE FROM inactive_iot_table WHERE _inactive_iot_id_ = ?");
            $stmt->bind_param('i', $iotId);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO active_iot_table (_active_iot_id_) VALUES (?)");
            $stmt->bind_param('i', $iotId);
            $stmt->execute();

            // Optional: Create a notification for the user
            $stmt = $conn->prepare("INSERT INTO notification_table (_user_id_, _notification_time_, _notification_title_, _notification_message_) VALUES (?, NOW(), 'IoT Request Approved', CONCAT('Your IoT device request has been approved. Your Device ID is: ', ?))");
            $stmt->bind_param('ii', $userId, $iotId);
            $stmt->execute();

            $_SESSION['success'] = "Request approved successfully. IoT device activated and location updated.";
        }

        if (isset($_POST['reject_request'])) {
            // Validate input
            $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);

            if ($requestId === false) {
                throw new Exception("Invalid request parameters");
            }

            // Remove from pending_request_table if exists
            $stmt = $conn->prepare("DELETE FROM pending_request_table WHERE _pending_request_id_ = ?");
            $stmt->bind_param('i', $requestId);
            $stmt->execute();

            // Get user ID before deleting request
            $userStmt = $conn->prepare("SELECT _user_id_ FROM request_table WHERE _request_id_ = ?");
            $userStmt->bind_param('i', $requestId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $userData = $userResult->fetch_assoc();

            // Add to declined_request_table
            $stmt = $conn->prepare("INSERT INTO declined_request_table (_declined_request_id_) VALUES (?)");
            $stmt->bind_param('i', $requestId);
            $stmt->execute();

            // Optional: Create a notification for the user
            if ($userData) {
                $stmt = $conn->prepare("INSERT INTO notification_table (_user_id_, _notification_time_, _notification_title_, _notification_message_) VALUES (?, NOW(), 'IoT Request Rejected', 'Your IoT device request has been rejected.')");
                $stmt->bind_param('i', $userData['_user_id_']);
                $stmt->execute();
            }

            $_SESSION['success'] = "Request rejected successfully.";
        }

        // New handler for toggling IoT device status
        if (isset($_POST['toggle_iot_status'])) {
            $iotId = filter_input(INPUT_POST, 'iot_id', FILTER_VALIDATE_INT);

            if ($iotId === false) {
                throw new Exception("Invalid IoT device ID");
            }

            // Check current status
            $statusQuery = $conn->prepare("
                SELECT 
                    CASE 
                        WHEN ai._active_iot_id_ IS NOT NULL THEN 'Active'
                        WHEN ii._inactive_iot_id_ IS NOT NULL THEN 'Inactive'
                        ELSE 'Unknown'
                    END as status
                FROM iot_table i
                LEFT JOIN active_iot_table ai ON i._iot_id_ = ai._active_iot_id_
                LEFT JOIN inactive_iot_table ii ON i._iot_id_ = ii._inactive_iot_id_
                WHERE i._iot_id_ = ?
            ");
            $statusQuery->bind_param('i', $iotId);
            $statusQuery->execute();
            $statusResult = $statusQuery->get_result();
            $statusData = $statusResult->fetch_assoc();

            if ($statusData['status'] === 'Active') {
                // Deactivate IoT device
                $conn->query("DELETE FROM active_iot_table WHERE _active_iot_id_ = $iotId");
                $conn->query("INSERT INTO inactive_iot_table (_inactive_iot_id_) VALUES ($iotId)");
                $_SESSION['success'] = "IoT device deactivated successfully.";
            } elseif ($statusData['status'] === 'Inactive') {
                // Activate IoT device
                $conn->query("DELETE FROM inactive_iot_table WHERE _inactive_iot_id_ = $iotId");
                $conn->query("INSERT INTO active_iot_table (_active_iot_id_) VALUES ($iotId)");
                $_SESSION['success'] = "IoT device activated successfully.";
            }
        }

        // New handler for resetting IoT device
        if (isset($_POST['reset_iot'])) {
            $iotId = filter_input(INPUT_POST, 'iot_id', FILTER_VALIDATE_INT);

            if ($iotId === false) {
                throw new Exception("Invalid IoT device ID");
            }

            // Reset IoT device:
            // 1. Set location to 0,0
            // 2. Clear label
            // 3. Set balance to 0
            // 4. Move to inactive status
            // 5. Remove from active table
            // 6. Insert into inactive table

            // Update IoT table location and label
            $updateIotStmt = $conn->prepare("UPDATE iot_table SET _iot_latitude_ = 0.000000, _iot_longitude_ = 0.000000, _iot_label_ = NULL WHERE _iot_id_ = ?");
            $updateIotStmt->bind_param('i', $iotId);
            $updateIotStmt->execute();

            // Reset balance for this IoT device
            $resetBalanceStmt = $conn->prepare("
                UPDATE balance_table 
                SET _current_balance_ = 0.00 
                WHERE _iot_id_ = ?
            ");
            $resetBalanceStmt->bind_param('i', $iotId);
            $resetBalanceStmt->execute();

            // Remove from active table if exists
            $conn->query("DELETE FROM active_iot_table WHERE _active_iot_id_ = $iotId");

            // Ensure it's in inactive table
            $conn->query("DELETE FROM inactive_iot_table WHERE _inactive_iot_id_ = $iotId");
            $conn->query("INSERT INTO inactive_iot_table (_inactive_iot_id_) VALUES ($iotId)");

            $_SESSION['success'] = "IoT device reset successfully.";
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing request: " . $e->getMessage();
    }

    // Clear the output buffer before redirecting
    ob_end_clean();

    // Rebuild the current query parameters without the POST data
    $redirectParams = $_GET;
    unset($redirectParams['submit']); // Remove any submission-related parameters

    // Redirect using built query parameters
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($redirectParams));
    exit();
}

try {
    // Pagination setup
    $page_requests = isset($_GET['page_requests']) ? (int)$_GET['page_requests'] : 1;
    $page_iot = isset($_GET['page_iot']) ? (int)$_GET['page_iot'] : 1;
    $limit = 10;
    $offset_requests = ($page_requests - 1) * $limit;
    $offset_iot = ($page_iot - 1) * $limit;

    // Search parameters
    $search_requests = isset($_GET['search_requests']) ? mysqli_real_escape_string($conn, $_GET['search_requests']) : '';
    $search_iot = isset($_GET['search_iot']) ? mysqli_real_escape_string($conn, $_GET['search_iot']) : '';

    // Build search conditions
    $requestSearchCondition = !empty($search_requests) ?
        " AND (r._request_id_ LIKE '%$search_requests%' OR 
               r._user_id_ LIKE '%$search_requests%' OR 
               r._iot_id_ LIKE '%$search_requests%')" : "";

    $iotSearchCondition = !empty($search_iot) ?
        " AND (i._iot_id_ LIKE '%$search_iot%' OR 
               i._iot_label_ LIKE '%$search_iot%')" : "";

    // Count total requests
    $countRequestsQuery = "SELECT COUNT(*) as total 
                          FROM request_table r 
                          WHERE 1=1 $requestSearchCondition";
    $countRequestsResult = mysqli_query($conn, $countRequestsQuery);
    $totalRequests = mysqli_fetch_assoc($countRequestsResult)['total'];
    $totalPagesRequests = ceil($totalRequests / $limit);

    // Count total IoT devices
    $countIoTQuery = "SELECT COUNT(*) as total 
                      FROM iot_table i 
                      WHERE 1=1 $iotSearchCondition";
    $countIoTResult = mysqli_query($conn, $countIoTQuery);
    $totalIoT = mysqli_fetch_assoc($countIoTResult)['total'];
    $totalPagesIoT = ceil($totalIoT / $limit);

    // Get Requests with pagination
    $requestsQuery = "SELECT r.*, 
       u._email_ as request_email,
       r._latitude_ as latitude,
       r._longitude_ as longitude,
       CASE 
           WHEN pr._pending_request_id_ IS NOT NULL THEN 'Pending'
           WHEN dr._declined_request_id_ IS NOT NULL THEN 'Declined'
           ELSE 'Unknown'
       END as status
        FROM request_table r
        JOIN user_table u ON r._user_id_ = u._user_id_
        JOIN iot_table i ON r._iot_id_ = i._iot_id_    -- Added JOIN with iot_table
        LEFT JOIN pending_request_table pr ON r._request_id_ = pr._pending_request_id_
        LEFT JOIN declined_request_table dr ON r._request_id_ = dr._declined_request_id_
        WHERE 1=1 $requestSearchCondition
        ORDER BY r._request_time_ DESC
        LIMIT ? OFFSET ?;";

    $stmt = mysqli_prepare($conn, $requestsQuery);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset_requests);
    mysqli_stmt_execute($stmt);
    $requests = mysqli_stmt_get_result($stmt);

    // Get IoT Devices with pagination
    $iotQuery = "SELECT i.*,
       u._email_ as last_user_email,
       CASE 
           WHEN ai._active_iot_id_ IS NOT NULL THEN 'Active'
           WHEN ii._inactive_iot_id_ IS NOT NULL THEN 'Inactive'
           WHEN ui._unpaid_iot_id_ IS NOT NULL THEN 'Unpaid'
           ELSE 'Unknown'
       END as status,
       CASE 
           WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
           WHEN w._water_id_ IS NOT NULL THEN 'Water'
           WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
           ELSE 'Unknown'
       END as utility_type
        FROM iot_table i
        LEFT JOIN (
            SELECT _iot_id_, _user_id_
            FROM usage_table ut1
            WHERE _usage_time_ = (
                SELECT MAX(_usage_time_)
                FROM usage_table ut2
                WHERE ut1._iot_id_ = ut2._iot_id_
            )
        ) last_usage ON i._iot_id_ = last_usage._iot_id_
        LEFT JOIN user_table u ON last_usage._user_id_ = u._user_id_
        LEFT JOIN active_iot_table ai ON i._iot_id_ = ai._active_iot_id_
        LEFT JOIN inactive_iot_table ii ON i._iot_id_ = ii._inactive_iot_id_
        LEFT JOIN unpaid_iot_table ui ON i._iot_id_ = ui._unpaid_iot_id_
        LEFT JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
        LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
        LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
        LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
        WHERE 1=1 $iotSearchCondition
        ORDER BY i._last_reported_time_ DESC
        LIMIT ? OFFSET ?;";

    $stmt = mysqli_prepare($conn, $iotQuery);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset_iot);
    mysqli_stmt_execute($stmt);
    $iotDevices = mysqli_stmt_get_result($stmt);

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}

ob_end_flush();
?>

<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/admin-base.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

<!-- main -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        if (isset($_POST['approve_request'])) {
            $requestId = intval($_POST['request_id']);
            $userId = intval($_POST['user_id']);
            $iotId = intval($_POST['iot_id']);

            // Remove from pending_request_table if exists
            $conn->query("DELETE FROM pending_request_table WHERE _pending_request_id_ = $requestId");

            // Insert initial usage record with 0.00 usage amount
            $stmt = $conn->prepare("INSERT INTO usage_table (_user_id_, _iot_id_, _usage_time_, _usage_amount_) VALUES (?, ?, NOW(), 0.00)");
            $stmt->bind_param('ii', $userId, $iotId);
            $stmt->execute();

            // Update IoT device status to active
            $conn->query("DELETE FROM inactive_iot_table WHERE _inactive_iot_id_ = $iotId");
            $conn->query("INSERT INTO active_iot_table (_active_iot_id_) VALUES ($iotId)");

            $_SESSION['success'] = "Request approved successfully. IoT device activated.";
        }

        if (isset($_POST['reject_request'])) {
            $requestId = intval($_POST['request_id']);

            // Remove from pending_request_table if exists
            $conn->query("DELETE FROM pending_request_table WHERE _pending_request_id_ = $requestId");

            // Add to declined_request_table
            $conn->query("INSERT INTO declined_request_table (_declined_request_id_) VALUES ($requestId)");

            $_SESSION['success'] = "Request rejected successfully.";
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing request: " . $e->getMessage();
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit();
}
?>
<main id="main-section">
    <h2 id="sub-div-header">IoT Control</h2>
    <div class="card mb-4" id="create-iot-card">
        <div class="card-body">
            <h5 class="card-title">Register IoT</h5>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="iot-type" class="form-label">Type</label>
                    <select class="form-select" id="iot-type" name="iot_type" required>
                        <option value="Electricity">Electricity</option>
                        <option value="Gas">Gas</option>
                        <option value="Water">Water</option>
                    </select>
                </div>
                <button type="submit" name="create_iot" class="btn btn-primary"
                        onclick="return confirm('Are you sure you want to register this IoT device?')">Register
                </button>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">IoT Requests</h5>
            <div class="mb-3">
                <input type="text" class="form-control" id="search-requests"
                       placeholder="🔍 Search requests..."
                       value="<?php echo htmlspecialchars($search_requests); ?>">
            </div>

            <div class="table-responsive">
                <table class="table table-borderless small">
                    <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>User ID</th>
                        <th>Email</th>
                        <th>IoT ID</th>
                        <th>Location</th>
                        <th>Request Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($requests)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['_request_id_']); ?></td>
                            <td><?php echo htmlspecialchars($row['_user_id_']); ?></td>
                            <td><?php echo htmlspecialchars($row['request_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['_iot_id_']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['latitude']); ?>,
                                <?php echo htmlspecialchars($row['longitude']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['_request_time_']); ?></td>
                            <td>
                                    <span class="badge <?php
                                    echo match ($row['status']) {
                                        'Active' => 'bg-success',
                                        'Pending' => 'bg-warning',
                                        'Declined' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <form method="POST" class="me-1">
                                        <input type="hidden" name="request_id"
                                               value="<?php echo htmlspecialchars($row['_request_id_']); ?>">
                                        <input type="hidden" name="user_id"
                                               value="<?php echo htmlspecialchars($row['_user_id_']); ?>">
                                        <input type="hidden" name="iot_id"
                                               value="<?php echo htmlspecialchars($row['_iot_id_']); ?>">
                                        <button type="submit" name="approve_request"
                                                class="btn btn-sm btn-success p-0 px-1"
                                                onclick="return confirm('Are you sure you want to approve this IoT request?')">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="request_id"
                                               value="<?php echo htmlspecialchars($row['_request_id_']); ?>">
                                        <button type="submit" name="reject_request"
                                                class="btn btn-sm btn-danger p-0 px-1"
                                                onclick="return confirm('Are you sure you want to reject this IoT request?')">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPagesRequests > 0): ?>
                <div class="d-flex justify-content-center" id="pagination-section">
                    <nav aria-label="Page navigation">
                        <ul class="pagination no-border">
                            <?php if ($page_requests > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_requests=<?php echo($page_requests - 1); ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>"
                                       aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif;

                            $start_page = max(1, min($page_requests - 2, $totalPagesRequests - 4));
                            $end_page = min($totalPagesRequests, $start_page + 4);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_requests=1&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif;
                            endif;

                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page_requests) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                       href="?page_requests=<?php echo $i; ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                            if ($end_page < $totalPagesRequests): ?>
                                <?php if ($end_page < $totalPagesRequests - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_requests=<?php echo $totalPagesRequests; ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>"><?php echo $totalPagesRequests; ?></a>
                                </li>
                            <?php endif;

                            if ($page_requests < $totalPagesRequests): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_requests=<?php echo($page_requests + 1); ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>"
                                       aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- IoT Devices Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Registered IoT Devices</h5>
            <div class="mb-3">
                <input type="text" class="form-control" id="search-iot"
                       placeholder="🔍 Search IoT devices..."
                       value="<?php echo htmlspecialchars($search_iot); ?>">
            </div>

            <div class="table-responsive">
                <table class="table table-borderless small">
                    <thead>
                    <tr>
                        <th>IoT ID</th>
                        <th>Label</th>
                        <th>Last User Email</th>
                        <th>Utility Type</th>
                        <th>Location</th>
                        <th>Last Reported</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($iotDevices)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['_iot_id_']); ?></td>
                            <td><?php echo !empty($row['_iot_label_']) ? htmlspecialchars($row['_iot_label_']) : 'N/A'; ?></td>
                            <td><?php echo !empty($row['last_user_email']) ? htmlspecialchars($row['last_user_email']) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['utility_type']); ?></td>
                            <td>
                                <?php
                                echo htmlspecialchars($row['_iot_latitude_']) . ', ' .
                                    htmlspecialchars($row['_iot_longitude_']);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['_last_reported_time_']); ?></td>
                            <td>
                                    <span class="badge <?php
                                    echo match ($row['status']) {
                                        'Active' => 'bg-success',
                                        'Inactive' => 'bg-warning',
                                        'Unpaid' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <form method="POST" class="me-1">
                                        <input type="hidden" name="iot_id"
                                               value="<?php echo htmlspecialchars($row['_iot_id_']); ?>">
                                        <button type="submit" name="toggle_iot_status" class="btn btn-sm <?php
                                        echo $row['status'] === 'Active' ? 'btn-warning' : 'btn-success';
                                        ?> p-0 px-1"
                                                onclick="return confirm('Are you sure you want to <?php
                                                echo $row['status'] === 'Active' ? 'deactivate' : 'activate';
                                                ?> this IoT device?')">
                                            <?php echo $row['status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="me-1">
                                        <input type="hidden" name="iot_id"
                                               value="<?php echo htmlspecialchars($row['_iot_id_']); ?>">
                                        <button type="submit" name="reset_iot" class="btn btn-sm btn-danger p-0 px-1"
                                                onclick="return confirm('Are you sure you want to reset this IoT device? This will clear its location, label, and balance.')">
                                            Reset
                                        </button>
                                    </form>
                                    <button class="btn btn-link p-0" onclick="editIoT(<?php echo $row['_iot_id_']; ?>)">
                                        <img src="../img/edit-svgrepo-com.svg" alt="Edit"
                                             style="width: 16px; height: 16px;">
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPagesIoT > 0): ?>
                <div class="d-flex justify-content-center" id="pagination-section">
                    <nav aria-label="Page navigation">
                        <ul class="pagination no-border">
                            <?php if ($page_iot > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_iot=<?php echo($page_iot - 1); ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>"
                                       aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif;

                            $start_page = max(1, min($page_iot - 2, $totalPagesIoT - 4));
                            $end_page = min($totalPagesIoT, $start_page + 4);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_iot=1&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif;
                            endif;

                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page_iot) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                       href="?page_iot=<?php echo $i; ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                            if ($end_page < $totalPagesIoT): ?>
                                <?php if ($end_page < $totalPagesIoT - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_iot=<?php echo $totalPagesIoT; ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>"><?php echo $totalPagesIoT; ?></a>
                                </li>
                            <?php endif;

                            if ($page_iot < $totalPagesIoT): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                       href="?page_iot=<?php echo($page_iot + 1); ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>"
                                       aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- footer -->
<?php
require_once './admin-footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
