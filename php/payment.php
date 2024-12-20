<?php
ob_start();
global $conn;
session_start();
require_once './db-connection.php';
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
    <link rel="stylesheet" href="../css/payment.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<?php

$error = null;
$user_id = $_SESSION['_user_id_'] ?? null;
$iot_id = $_GET['iot_id'] ?? null;
$status = $_GET['status'] ?? null;
$errorMessage = $_GET['message'] ?? null;
$device = null;

try {
    if (!$iot_id) {
        header("Location: dashboard.php");
        exit;
    }

    // Process Payment
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
        if (!isset($_SESSION['last_payment_time']) ||
            (time() - $_SESSION['last_payment_time'] > 5)) {

            $amount = floatval($_POST['amount']);

            if ($amount < 500) {
                header("Location: payment.php?iot_id=" . $iot_id . "&status=error&message=" . urlencode("Transaction amount must be at least 500 taka"));
                exit;
            }

            if ($amount > 20000) {
                header("Location: payment.php?iot_id=" . $iot_id . "&status=error&message=" . urlencode("Transaction amount cannot exceed 20,000 taka"));
                exit;
            }

            mysqli_begin_transaction($conn);

            try {
                $dateTime = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
                $currentDateTime = $dateTime->format('Y-m-d H:i:s');

                $rechargeQuery = "INSERT INTO recharge_table (_user_id_, _iot_id_, _recharge_time_, _recharge_amount_) 
                                VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $rechargeQuery);
                mysqli_stmt_bind_param($stmt, "iiss", $user_id, $iot_id, $currentDateTime, $amount);
                mysqli_stmt_execute($stmt);

                $balanceQuery = "UPDATE balance_table 
                                SET _current_balance_ = _current_balance_ + ? 
                                WHERE _user_id_ = ? AND _iot_id_ = ?";
                $stmt = mysqli_prepare($conn, $balanceQuery);
                mysqli_stmt_bind_param($stmt, "dii", $amount, $user_id, $iot_id);
                mysqli_stmt_execute($stmt);

                $balanceCheckQuery = "SELECT _current_balance_ FROM balance_table 
                                    WHERE _user_id_ = ? AND _iot_id_ = ?";
                $stmt = mysqli_prepare($conn, $balanceCheckQuery);
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $iot_id);
                mysqli_stmt_execute($stmt);
                $balance = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['_current_balance_'];

                if ($balance > 0) {
                    $deleteUnpaidQuery = "DELETE FROM unpaid_iot_table WHERE _unpaid_iot_id_ = ?";
                    $stmt = mysqli_prepare($conn, $deleteUnpaidQuery);
                    mysqli_stmt_bind_param($stmt, "i", $iot_id);
                    mysqli_stmt_execute($stmt);

                    $insertActiveQuery = "INSERT IGNORE INTO active_iot_table (_active_iot_id_) VALUES (?)";
                    $stmt = mysqli_prepare($conn, $insertActiveQuery);
                    mysqli_stmt_bind_param($stmt, "i", $iot_id);
                    mysqli_stmt_execute($stmt);
                }

                mysqli_commit($conn);
                $_SESSION['last_payment_time'] = time();
                header("Location: payment.php?iot_id=" . $iot_id . "&status=success");
                exit;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                header("Location: payment.php?iot_id=" . $iot_id . "&status=error&message=" . urlencode($e->getMessage()));
                exit;
            }
        } else {
            header("Location: payment.php?iot_id=" . $iot_id);
            exit;
        }
    }

    $deviceQuery = "SELECT 
        i._iot_label_,
        i._iot_id_,
        CASE 
            WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
            WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
            WHEN w._water_id_ IS NOT NULL THEN 'Water'
        END AS _type_,
        COALESCE(b._current_balance_, 0) AS _current_balance_
        FROM iot_table i
        LEFT JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
        LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
        LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
        LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
        LEFT JOIN balance_table b ON i._iot_id_ = b._iot_id_ AND b._user_id_ = ?
        WHERE i._iot_id_ = ?";

    $stmt = mysqli_prepare($conn, $deviceQuery);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $iot_id);
    mysqli_stmt_execute($stmt);
    $device = mysqli_stmt_get_result($stmt)->fetch_assoc();

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<main id="main-section">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="container p-4">
        <form class="card p-4" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-3">Your Device</h4>
                    <label for="device-info" class="form-label">
                        <?= htmlspecialchars($device['_iot_id_']) ?>
                        (<?= htmlspecialchars($device['_type_']) ?> - <?= htmlspecialchars($device['_iot_label_']) ?>)
                    </label>
                    <p>Current Balance: <?= htmlspecialchars($device['_current_balance_']) ?> ৳</p>
                    <input type="hidden" name="iot_id" value="<?= htmlspecialchars($iot_id) ?>">
                </div>
                <div class="col-md-6">
                    <h4 class="mb-3">Amount</h4>
                    <input
                        type="number"
                        class="form-control"
                        name="amount"
                        min="500"
                        max="20000"
                        step="0.01"
                        placeholder="Enter amount (৳500 - ৳20,000)"
                        required
                    >
                    <div class="invalid-feedback">
                        Amount must be between ৳500 and ৳20,000
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h4 class="mb-3">Payment Method</h4>
            <div class="payment-methods">
                <div class="payment-section">
                    <h5 class="section-header">Mobile Banking</h5>
                    <div class="mobile-payments">
                        <div class="payment-option" data-method="bkash">
                            <img src="../img/bkash.png" alt="bKash" onerror="this.src='../img/placeholder.png'">
                            <span>bKash</span>
                        </div>
                        <div class="payment-option" data-method="nagad">
                            <img src="../img/nagad.png" alt="Nagad" onerror="this.src='../img/placeholder.png'">
                            <span>Nagad</span>
                        </div>
                        <div class="payment-option" data-method="rocket">
                            <img src="../img/rocket.png" alt="Rocket" onerror="this.src='../img/placeholder.png'">
                            <span>Rocket</span>
                        </div>
                        <div class="payment-option" data-method="upay">
                            <img src="../img/upay.png" alt="Upay" onerror="this.src='../img/placeholder.png'">
                            <span>Upay</span>
                        </div>
                    </div>
                </div>

                <div class="payment-section">
                    <h5 class="section-header">Card Payment</h5>
                    <div id="card-payment-section">
                        <div class="my-3">
                            <div class="form-check">
                                <input id="credit" name="paymentMethod" type="radio" class="form-check-input" checked
                                       required>
                                <label class="form-check-label" for="credit">Credit card</label>
                            </div>
                            <div class="form-check">
                                <input id="debit" name="paymentMethod" type="radio" class="form-check-input" required>
                                <label class="form-check-label" for="debit">Debit card</label>
                            </div>
                        </div>

                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label for="cc-name" class="form-label">Name on card</label>
                                <input type="text" class="form-control" id="cc-name"
                                       placeholder="Enter full name as on card" required>
                                <small class="text-body-secondary">Full name as displayed on card</small>
                            </div>

                            <div class="col-md-6">
                                <label for="cc-number" class="form-label">Card number</label>
                                <input type="text" class="form-control" id="cc-number" placeholder="XXXX XXXX XXXX XXXX"
                                       maxlength="19" required>
                            </div>

                            <div class="col-md-3">
                                <label for="cc-expiration" class="form-label">Expiration</label>
                                <input type="text" class="form-control" id="cc-expiration" placeholder="MM/YY"
                                       maxlength="5" required>
                            </div>

                            <div class="col-md-3">
                                <label for="cc-cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cc-cvv" placeholder="3 or 4-digit code"
                                       maxlength="4" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <button class="w-100 btn btn-primary btn-lg" type="submit">Check Out</button>
        </form>
    </div>
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- Success Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #63ba5d; color: white;">
                <h5 class="modal-title" id="paymentSuccessModalLabel">Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#63ba5d"
                     class="bi bi-check-circle-fill mb-4" viewBox="0 0 16 16">
                    <path
                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <h4>Payment Successful!</h4>
                <p class="text-muted mb-0">Your payment has been processed successfully.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn px-4" style="background-color: #63ba5d; color: white;"
                        data-bs-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="paymentErrorModal" tabindex="-1" aria-labelledby="paymentErrorModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="paymentErrorModalLabel">Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor"
                     class="bi bi-exclamation-circle-fill text-danger mb-4" viewBox="0 0 16 16">
                    <path
                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                </svg>
                <h4>Oops! Something went wrong</h4>
                <p class="text-muted mb-0" id="errorMessage"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- scripts -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/payment.js"></script>

<?php if ($status === 'success'): ?>
    <script>
        const paymentStatus = 'success';
        const redirectUrl = 'dashboard.php';
    </script>
<?php elseif ($status === 'error'): ?>
    <script>
        const paymentStatus = 'error';
        const errorMessage = '<?= htmlspecialchars($errorMessage) ?>';
        const redirectUrl = 'dashboard.php';
    </script>
<?php endif; ?>

</body>
</html>
<?php ob_end_flush(); ?>
