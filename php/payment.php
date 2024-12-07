<?php
session_start();
require_once './db-connection.php';

if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];
$iot_id = isset($_GET['iot_id']) ? $_GET['iot_id'] : null;

if (!$iot_id) {
    header("Location: dashboard.php");
    exit;
}

try {
    // Get device details
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
        if (!isset($_SESSION['last_payment_time']) || 
            (isset($_SESSION['last_payment_time']) && time() - $_SESSION['last_payment_time'] > 5)) {
            
            $amount = floatval($_POST['amount']);
            $currentDateTime = date('Y-m-d H:i:s');
            
            mysqli_begin_transaction($conn);
            
            try {
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

    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $errorMessage = isset($_GET['message']) ? $_GET['message'] : null;
    
    if ($status === 'success') {
        echo "<script>
            var paymentStatus = 'success';
            var redirectUrl = 'dashboard.php';
        </script>";
    } else if ($status === 'error') {
        echo "<script>
            var paymentStatus = 'error';
            var errorMessage = '" . htmlspecialchars($errorMessage) . "';
            var redirectUrl = 'dashboard.php';
        </script>";
    }

    // Get notifications for header
    $notificationQuery = "SELECT * FROM notification_table
                         WHERE _user_id_ = ? OR _user_id_ IS NULL
                         ORDER BY _notification_time_ DESC";
    $stmt = mysqli_prepare($conn, $notificationQuery);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $notifications = mysqli_stmt_get_result($stmt);

    // Get user picture for header
    $pictureQuery = "SELECT _profile_picture_ FROM user_table WHERE _user_id_ = ?";
    $stmt = mysqli_prepare($conn, $pictureQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $picture = mysqli_stmt_get_result($stmt);

    if (($row = mysqli_fetch_assoc($picture)) && (!empty($row['_profile_picture_']) && $row['_profile_picture_'] !== NULL)) {
        $pictureData = $row['_profile_picture_'];
        $base64Image = base64_encode($pictureData);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "../img/user-rounded-svgrepo-com.jpg";
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Payment</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/payment.css">
    <link rel="stylesheet" href="../css/animation.css">

</head>

<!-- body -->

<body>
    <!-- header -->
    <header class="border-bottom" id="header-section">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../index.php" class="d-flex align-items-center mb-lg-0">
                    <img src="../img/CLIX.svg" id="header-logo" alt="Logo" class="img-fluid">
                </a>
                
                <!-- Navbar -->
                <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <ul class="nav">
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./pay.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    </ul>
                </nav>

                <!-- Notification, Mobile Navbar and User Section -->
                <div class="d-flex align-items-center">
                    <!-- Mobile Navbar Toggle -->
                    <button class="navbar-toggler d-lg-none" type="button" style="width: 50px; height: 50px;" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </span>
                    </button>

                    <!-- Notifications -->
                    <div class="dropdown text-end me-2" id="notification-icon">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17px" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <?php while ($row = mysqli_fetch_assoc($notifications)) : ?>
                                <li><a class="dropdown-item small" href="#"><?= htmlspecialchars($row['_notification_message_']); ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- User Picture -->
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?php echo $imageSrc; ?>" alt="User" class="rounded-circle" style="width: 36px; height: 36px;">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="./profile.php">Profile</a></li>
                            <li><a class="dropdown-item small" href="./settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="./logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Collapsible Mobile Menu -->
            <div class="collapse" id="mobileNav">
                <nav class="navbar-nav">
                    <ul class="nav flex-column text-center">
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./pay.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- main -->
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
                        <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
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
                                    <input id="credit" name="paymentMethod" type="radio" class="form-check-input" checked required>
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
                                    <input type="text" class="form-control" id="cc-name" required>
                                    <small class="text-body-secondary">Full name as displayed on card</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="cc-number" class="form-label">Card number</label>
                                    <input type="text" class="form-control" id="cc-number" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="cc-expiration" class="form-label">Expiration</label>
                                    <input type="text" class="form-control" id="cc-expiration" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="cc-cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cc-cvv" required>
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
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img src="../img/CLIX.svg" width="46">
                <small class="d-block mb-3 text-body-secondary">©2024</small>
                <p class="small text-body-secondary">
                    Why CLIX?<br>
                    Convenient Living<br>
                    Integrated Experience
                </p>
            </div>
            <div class="col-3">
                <h5>Links</h5>
                <ul class=" list-unstyled">
                    <li><a class="link-secondary text-decoration-none small" href="#">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="#">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Phone: +8801712345678</a></li>
                </ul>
            </div>
        </div>
    </footer>
    
    <!-- Success Modal -->
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #63ba5d; color: white;">
                    <h5 class="modal-title" id="paymentSuccessModalLabel">Success!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#63ba5d" class="bi bi-check-circle-fill mb-4" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <h4>Payment Successful!</h4>
                    <p class="text-muted mb-0">Your payment has been processed successfully.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn px-4" style="background-color: #63ba5d; color: white;" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="paymentErrorModal" tabindex="-1" aria-labelledby="paymentErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="paymentErrorModalLabel">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-circle-fill text-danger mb-4" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
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

    <!-- script -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentOptions = document.querySelectorAll('.payment-option');
            const cardPaymentSection = document.getElementById('card-payment-section');
            const cardInputs = cardPaymentSection.querySelectorAll('input');
            let selectedMobilePayment = null;

            function resetMobilePayments() {
                paymentOptions.forEach(option => {
                    option.classList.remove('selected');
                });
            }

            function toggleCardPaymentSection(disable) {
                if (disable) {
                    cardPaymentSection.classList.add('disabled');
                    cardInputs.forEach(input => {
                        input.disabled = true;
                    });
                } else {
                    cardPaymentSection.classList.remove('disabled');
                    cardInputs.forEach(input => {
                        input.disabled = false;
                    });
                }
            }

            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const method = this.getAttribute('data-method');
                    
                    if (selectedMobilePayment === method) {
                        // Deselect current mobile payment
                        resetMobilePayments();
                        toggleCardPaymentSection(false);
                        selectedMobilePayment = null;
                    } else {
                        // Select new mobile payment
                        resetMobilePayments();
                        this.classList.add('selected');
                        toggleCardPaymentSection(true);
                        selectedMobilePayment = method;
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof paymentStatus !== 'undefined') {
                if (paymentStatus === 'success') {
                    const successModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
                    
                    // Add event listener for when modal is hidden
                    document.getElementById('paymentSuccessModal').addEventListener('hidden.bs.modal', () => {
                        window.location.href = 'dashboard.php';
                    });
                    
                    successModal.show();
                } else if (paymentStatus === 'error') {
                    const errorModal = new bootstrap.Modal(document.getElementById('paymentErrorModal'));
                    document.getElementById('errorMessage').textContent = errorMessage || "An unexpected error occurred.";
                    
                    // Add event listener for when modal is hidden
                    document.getElementById('paymentErrorModal').addEventListener('hidden.bs.modal', () => {
                        window.location.href = 'dashboard.php';
                    });
                    
                    errorModal.show();
                }
            }
        });
    </script>

</body>

</html>