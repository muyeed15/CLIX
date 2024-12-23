<?php
global $conn;
session_start();
require_once './db-connection.php';

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['user_id'] ?? 0;

$iot_id = filter_input(INPUT_GET, 'iot_id', FILTER_VALIDATE_INT);

if (!$iot_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch current IoT label
$current_label = '';
try {
    $label_query = "SELECT _iot_label_ FROM iot_table WHERE _iot_id_ = ?";
    $label_stmt = mysqli_prepare($conn, $label_query);
    mysqli_stmt_bind_param($label_stmt, "i", $iot_id);

    if (mysqli_stmt_execute($label_stmt)) {
        $result = mysqli_stmt_get_result($label_stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $current_label = $row['_iot_label_'];
        }
    }
} catch (Exception $e) {
    $error_message = "Failed to fetch current label";
}

$error_message = '';
$success_message = '';

// Handle device label update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_label = filter_input(INPUT_POST, 'label', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($new_label)) {
        $error_message = "Invalid device label";
    } else {
        try {
            mysqli_begin_transaction($conn);
            // Update device label
            $update_query = "UPDATE iot_table SET _iot_label_ = ? WHERE _iot_id_ = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "si", $new_label, $iot_id);

            if (!mysqli_stmt_execute($update_stmt)) {
                throw new Exception('Failed to update device label');
            }

            mysqli_commit($conn);
            $success_message = "Device label updated successfully";
            $current_label = $new_label; // Update the current label after successful update

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}
?>
<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Payment</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<main id="main-section" style="position: relative;">
    <h2 id="sub-div-header">IoT on CLIX Network</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo sanitize_input($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo sanitize_input($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4" id="create-iot-card" style="display: inline-block; vertical-align: top; width: 70%;">
        <div class="card-body">
            <h5 class="card-title">Update IoT Label</h5>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?iot_id=' . $iot_id; ?>">
                <div class="mb-3">
                    <label for="iot-header" class="form-label">IoT ID</label>
                    <input type="text" class="form-control" id="iot-header" value="<?php echo $iot_id; ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="label" class="form-label">Label</label>
                    <input type="text" class="form-control" id="label" name="label"
                           value="<?php echo !empty($current_label) ? htmlspecialchars($current_label) : ''; ?>"
                           required maxlength="255" placeholder="Enter label">
                </div>
                <button type="submit" class="btn btn-primary">Update Label</button>
                <a href="./dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <img src="../img/NicePng_meter-png_905785.png" alt="IoT Illustration"
         style="display: inline-block; vertical-align: top; margin-left: 80px; width: 19%; max-width: 300px;">
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- scripts -->
<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
