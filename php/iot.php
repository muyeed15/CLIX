<?php
session_start();
require_once './db-connection.php';

// Initialize variables
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['_user_id_'])) {
        $message = 'Please log in to submit a request.';
        $messageType = 'danger';
    } else {
        $userId = $_SESSION['_user_id_'];
        $iotId = trim($_POST['iot-id']);
        $iotLabel = trim($_POST['iot-label']);
        $requestTime = date('Y-m-d H:i:s');
        $requestStatus = 'pending';

        try {
            // Start transaction
            mysqli_begin_transaction($conn);

            // First, insert into iot_table
            $insertIotQuery = "INSERT INTO iot_table (_utility_id_, _iot_label_, _iot_latitude_, _iot_longitude_, _last_reported_time_)
                             VALUES (?, ?, 0, 0, NOW())";
            $stmt = mysqli_prepare($conn, $insertIotQuery);

            $utilityId = 1;
            mysqli_stmt_bind_param($stmt, "is", $utilityId, $iotLabel);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to create IoT entry');
            }

            $newIotId = mysqli_insert_id($conn);

            // Insert into inactive_iot_table
            $inactiveQuery = "INSERT INTO inactive_iot_table (_inactive_iot_id_) VALUES (?)";
            $stmt = mysqli_prepare($conn, $inactiveQuery);
            mysqli_stmt_bind_param($stmt, "i", $newIotId);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to create inactive IoT entry');
            }

            // Insert into request_table
            $insertRequestQuery = "INSERT INTO request_table (_user_id_, _iot_id_, _request_time_, _request_status_)
                                 VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertRequestQuery);
            mysqli_stmt_bind_param($stmt, "iiss", $userId, $newIotId, $requestTime, $requestStatus);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to create request');
            }

            $requestId = mysqli_insert_id($conn);

            // Insert into pending_request_table
            $pendingQuery = "INSERT INTO pending_request_table (_pending_request_id_) VALUES (?)";
            $stmt = mysqli_prepare($conn, $pendingQuery);
            mysqli_stmt_bind_param($stmt, "i", $requestId);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to create pending request');
            }

            // Create notification
            $notificationTitle = "New IoT Request";
            $notificationMessage = "Your request for IoT device with label '$iotLabel' has been submitted and is pending approval.";

            $notifyQuery = "INSERT INTO notification_table (_user_id_, _notification_time_, _notification_title_, _notification_message_)
                           VALUES (?, NOW(), ?, ?)";
            $stmt = mysqli_prepare($conn, $notifyQuery);
            mysqli_stmt_bind_param($stmt, "iss", $userId, $notificationTitle, $notificationMessage);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to create notification');
            }

            // Commit transaction
            mysqli_commit($conn);

            $message = 'IoT request submitted successfully!';
            $messageType = 'success';

        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: IoT on CLIX Network</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/animation.css">
    <link rel="stylesheet" href="../css/iot.css">
</head>

<body>
<!-- Header -->
<?php require_once './header.php'; ?>

<!-- Main Section -->
<main id="main-section" style="position: relative;">
    <h2 id="sub-div-header">IoT on CLIX Network</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4" id="create-iot-card" style="display: inline-block; vertical-align: top; width: 70%;">
        <div class="card-body">
            <h5 class="card-title">Register IoT</h5>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="iot-id" class="form-label">IoT ID</label>
                    <input type="text"
                           class="form-control"
                           id="iot-id"
                           name="iot-id"
                           required
                           pattern="[0-9]+"
                           placeholder="Enter IoT ID (numbers only)"
                           value="<?php echo isset($_POST['iot-id']) ? htmlspecialchars($_POST['iot-id']) : ''; ?>">
                    <div class="invalid-feedback">
                        Please provide a valid IoT ID (numbers only).
                    </div>
                </div>
                <div class="mb-3">
                    <label for="iot-label" class="form-label">Label</label>
                    <input type="text"
                           class="form-control"
                           id="iot-label"
                           name="iot-label"
                           required
                           placeholder="Enter label"
                           value="<?php echo isset($_POST['iot-label']) ? htmlspecialchars($_POST['iot-label']) : ''; ?>">
                    <div class="invalid-feedback">
                        Please provide a label for the IoT device.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Request</button>
            </form>
        </div>
    </div>
    <img src="../img/NicePng_meter-png_905785.png" alt="IoT Illustration" style="display: inline-block; vertical-align: top; margin-left: 80px; width: 19%; max-width: 300px;">
</main>

<!-- Footer -->
<?php require_once './footer.php'; ?>

<!-- Scripts -->
<script src="../js/bootstrap.bundle.js"></script>
<script>
    // Form validation script
    (function() {
        'use strict';

        const forms = document.querySelectorAll('.needs-validation');

        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>
