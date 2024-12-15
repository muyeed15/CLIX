<?php
session_start();
require_once './db-connection.php';

if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];

$clientCheckQuery = "SELECT c._client_id_ 
                    FROM client_table c 
                    WHERE c._client_id_ = ?";

try {
    $stmt = mysqli_prepare($conn, $clientCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        header("Location: access-denied.php");
        exit;
    }

    // Notification
    $notificationQuery = "SELECT * FROM notification_table
                        WHERE _user_id_ = ? OR _user_id_ IS NULL
                        ORDER BY _notification_time_ DESC
                        LIMIT 10";

    $stmt = mysqli_prepare($conn, $notificationQuery);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $notifications = mysqli_stmt_get_result($stmt);

    // User Picture
    $pictureQuery = "SELECT _profile_picture_ FROM user_table
                    WHERE _user_id_ = ?";

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

    mysqli_stmt_close($stmt);

    // IoT Usage
    $usageQuery = "SELECT 
                CASE 
                    WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                    WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
                    WHEN w._water_id_ IS NOT NULL THEN 'Water'
                END AS _type_,
                i._iot_id_,
                i._iot_label_ AS _label_,
                COALESCE(u_current._usage_amount_, 0) AS _last_usage_,
                COALESCE(SUM(u_total._usage_amount_), 0) AS _total_usage_,
                COALESCE(b._current_balance_, 0) AS _balance_,
                CASE 
                    WHEN b._current_balance_ <= 0 OR u._unpaid_iot_id_ IS NOT NULL THEN 'Unpaid'
                    WHEN b._current_balance_ > 0 AND a._active_iot_id_ IS NOT NULL THEN 'Active'
                    WHEN b._current_balance_ > 0 AND ia._inactive_iot_id_ IS NOT NULL THEN 'Inactive'
                    ELSE 'Unknown'
                END AS _status_
            FROM 
                iot_table i
                LEFT JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
                LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
                LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
                LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
                LEFT JOIN (
                    SELECT _iot_id_, _usage_amount_
                    FROM usage_table
                    WHERE (_iot_id_, _usage_time_) IN (
                        SELECT _iot_id_, MAX(_usage_time_) 
                        FROM usage_table 
                        GROUP BY _iot_id_
                    )
                ) u_current ON i._iot_id_ = u_current._iot_id_
                LEFT JOIN usage_table u_total ON i._iot_id_ = u_total._iot_id_
                LEFT JOIN balance_table b ON i._iot_id_ = b._iot_id_
                LEFT JOIN active_iot_table a ON i._iot_id_ = a._active_iot_id_
                LEFT JOIN inactive_iot_table ia ON i._iot_id_ = ia._inactive_iot_id_
                LEFT JOIN unpaid_iot_table u ON i._iot_id_ = u._unpaid_iot_id_
                INNER JOIN balance_table user_iot ON i._iot_id_ = user_iot._iot_id_
            WHERE 
                user_iot._user_id_ = ?
            GROUP BY 
                _type_, 
                i._iot_id_, 
                i._iot_label_,
                u_current._usage_amount_,
                b._current_balance_,
                a._active_iot_id_,
                ia._inactive_iot_id_,
                u._unpaid_iot_id_
            ORDER BY 
                _type_, 
                i._iot_id_;";

    $stmt = mysqli_prepare($conn, $usageQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $usageSummary = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}
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
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
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
                        <li><a href="./dashboard.php" class="nav-link px-3 link-secondary">Dashboard</a></li>
                        <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
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
                        <li><a href="./dashboard.php" class="nav-link px-3 link-secondary">Dashboard</a></li>
                        <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- main -->
    <main id="main-section">
        <h2 id="sub-div-header">Dashboard</h2>

        <div style="display: flex; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 0 0 66.666%; padding: 0.5%;">
                <div class="card">
                    <div class="card-body">
                        <canvas id="chLine"></canvas>
                    </div>
                </div>
            </div>
            <div style="flex: 0 0 33.333%; padding: 0.5%;">
                <div class="card">
                    <div class="card-body">
                        <canvas id="chDonut1"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="d-flex justify-content-between align-items-center">
                <h2 id="sub-div-header">Your Devices</h2>
                <a href="./iot.php">
                <img src="../img/add-circle-svgrepo-com.svg" width="20vw">
                </a>
            </div>
            <table class="table table-borderless" id="iot-table" style="color: #282828;">
                <thead id="iot-thead">
                    <tr>
                        <th>Device</th>
                        <th>ID</th>
                        <th>Label</th>
                        <th>Usage</th>
                        <th>Total Usage</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($usageSummary)) : ?>
                        <tr>
                            <td>
                                <?php
                                $utilityIcon = '';
                                $outageType = '';
                                if (isset($row['_type_'])) {
                                    switch($row['_type_']) {
                                        case 'Gas':
                                            $utilityIcon = '../img/gas-costs-svgrepo-com.svg';
                                            $outageType = 'Gas';
                                            break;
                                        case 'Water':
                                            $utilityIcon = '../img/water-fee-svgrepo-com.svg';
                                            $outageType = 'Water';
                                            break;
                                        case 'Electricity':
                                            $utilityIcon = '../img/hydropower-coal-svgrepo-com.svg';
                                            $outageType = 'Electricity';
                                            break;
                                    }
                                }
                                ?>
                                <img class="utility-svg" src="<?php echo $utilityIcon; ?>" alt="<?php echo $outageType; ?>">
                            </td>
                            <td><?= htmlspecialchars($row['_iot_id_']); ?></td>
                            <td><?= htmlspecialchars($row['_label_']); ?></td>
                            <td>
                                <?php 
                                    if ($row['_type_'] === 'Gas') {
                                        echo htmlspecialchars($row['_last_usage_']) . " m³";
                                    } elseif ($row['_type_'] === 'Water') {
                                        echo htmlspecialchars($row['_last_usage_']) . " L";
                                    } elseif ($row['_type_'] === 'Electricity') {
                                        echo htmlspecialchars($row['_last_usage_']) . " kWh";
                                    } else {
                                        echo htmlspecialchars($row['_last_usage_']);
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    if ($row['_type_'] === 'Gas') {
                                        echo htmlspecialchars($row['_total_usage_']) . " m³";
                                    } elseif ($row['_type_'] === 'Water') {
                                        echo htmlspecialchars($row['_total_usage_']) . " L";
                                    } elseif ($row['_type_'] === 'Electricity') {
                                        echo htmlspecialchars($row['_total_usage_']) . " kWh";
                                    } else {
                                        echo htmlspecialchars($row['_total_usage_']);
                                    }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['_balance_']) . " ৳"; ?></td>
                            <td 
                                <?php 
                                    if ($row['_status_'] === 'Inactive') {
                                        echo 'style="color: #a3a3a3;"';
                                    } elseif ($row['_status_'] === 'Unpaid') {
                                        echo 'style="color: #f05959;"';
                                    } elseif ($row['_status_'] === 'Active') {
                                        echo 'style="color: #53cf6b;"';
                                    }
                                ?>
                                    >
                                <?= htmlspecialchars($row['_status_']); ?>
                            </td>
                            <td>
                                <a href="./payment.php?iot_id=<?= htmlspecialchars($row['_iot_id_']); ?>" class="d-flex px-3">
                                    <img class="utility-svg-pay" src="../img/creadit-card-debit-svgrepo-green.svg">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img class="footer-logo" src="../img/CLIX.svg">
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
                    <li><a class="link-secondary text-decoration-none small" href="./about.php">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./contact.php">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./privacy.php">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./term.php">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./faq.php">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="mailto:clix@mail.com">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="">Phone: +8801712345678</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- script -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/chart.js"></script>
    <script src="../js/chart.script.js"></script>

</body>
</html>
