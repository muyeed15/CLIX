<?php
session_start();
require_once './db-connection.php';

// Check if user is logged in
if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];

// Check if user is admin
$clientCheckQuery = "SELECT a._admin_id_ 
                    FROM admin_table a 
                    WHERE a._admin_id_ = ?";

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

    // 1. Monthly utility usage trends
    $monthlyQuery = "SELECT 
        DATE_FORMAT(u._usage_time_, '%Y-%m') as month,
        SUM(CASE WHEN ut._utility_id_ = 1 THEN u._usage_amount_ END) as water_usage,
        SUM(CASE WHEN ut._utility_id_ = 2 THEN u._usage_amount_ END) as electricity_usage,
        SUM(CASE WHEN ut._utility_id_ = 3 THEN u._usage_amount_ END) as gas_usage
    FROM usage_table u
    JOIN iot_table i ON u._iot_id_ = i._iot_id_
    JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12";

    $monthlyResult = mysqli_query($conn, $monthlyQuery);
    $monthlyData = array();
    while ($row = mysqli_fetch_assoc($monthlyResult)) {
        $monthlyData[] = $row;
    }

    // 2. Active IoT devices by utility type
    $iotQuery = "SELECT 
        ut._utility_id_,
        COUNT(CASE WHEN EXISTS (SELECT 1 FROM active_iot_table WHERE _active_iot_id_ = i._iot_id_) THEN 1 END) as active_devices,
        COUNT(CASE WHEN EXISTS (SELECT 1 FROM inactive_iot_table WHERE _inactive_iot_id_ = i._iot_id_) THEN 1 END) as inactive_devices
    FROM iot_table i
    JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
    GROUP BY ut._utility_id_";

    $iotResult = mysqli_query($conn, $iotQuery);
    $iotData = array();
    while ($row = mysqli_fetch_assoc($iotResult)) {
        $iotData[] = $row;
    }

    // 3. User consumption patterns
    $userPatternQuery = "SELECT 
        DATE_FORMAT(u._usage_time_, '%H:00') as hour_of_day,
        AVG(u._usage_amount_) as avg_usage
    FROM usage_table u
    GROUP BY hour_of_day
    ORDER BY hour_of_day";

    $userPatternResult = mysqli_query($conn, $userPatternQuery);
    $userPatternData = array();
    while ($row = mysqli_fetch_assoc($userPatternResult)) {
        $userPatternData[] = $row;
    }

    // 4. Outage statistics
    $outageQuery = "SELECT 
        COUNT(CASE WHEN EXISTS (SELECT 1 FROM high_impact_table WHERE _high_impact_id_ = om._outage_map_id_) THEN 1 END) as high_impact,
        COUNT(CASE WHEN EXISTS (SELECT 1 FROM medium_impact_table WHERE _medium_impact_id_ = om._outage_map_id_) THEN 1 END) as medium_impact,
        COUNT(CASE WHEN EXISTS (SELECT 1 FROM low_impact_table WHERE _low_impact_id_ = om._outage_map_id_) THEN 1 END) as low_impact
    FROM outage_mapping_table om";

    $outageResult = mysqli_query($conn, $outageQuery);
    $outageData = mysqli_fetch_assoc($outageResult);

    // 5. User activity data
    $activityQuery = "SELECT 
        DATE_FORMAT(_log_time_, '%Y-%m-%d') as date,
        COUNT(*) as login_count
    FROM user_login_log_table
    GROUP BY date
    ORDER BY date DESC
    LIMIT 30";

    $activityResult = mysqli_query($conn, $activityQuery);
    $activityData = array();
    while ($row = mysqli_fetch_assoc($activityResult)) {
        $activityData[] = $row;
    }

    // 6. Feedback analysis
    $feedbackQuery = "SELECT 
        DATE_FORMAT(_feedback_time_, '%Y-%m') as month,
        COUNT(*) as feedback_count
    FROM feedback_table
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12";

    $feedbackResult = mysqli_query($conn, $feedbackQuery);
    $feedbackData = array();
    while ($row = mysqli_fetch_assoc($feedbackResult)) {
        $feedbackData[] = $row;
    }

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
    <!-- Header -->
    <header class="border-bottom" id="header-section">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../index.php" class="d-flex align-items-center mb-lg-0">
                    <img src="../img/CLIX-white.svg" id="header-logo" alt="Logo" class="img-fluid">
                </a>
                
                <!-- Navbar -->
                <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <ul class="nav">
                        <li><a href="./admin-dashboard.php" class="nav-link px-3 link-secondary">Dashboard</a></li>
                        <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                        <li><a href="./admin-user-control.php" class="nav-link px-3 link-body-emphasis">Client</a></li>
                        <li><a href="./admin-feedback.php" class="nav-link px-3 link-body-emphasis">Feedback</a></li>
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
                        <li><a href="./admin-dashboard.php" class="nav-link px-3 link-secondary">Dashboard</a></li>
                        <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                        <li><a href="./admin-user-control.php" class="nav-link px-3 link-body-emphasis">Client</a></li>
                        <li><a href="./admin-feedback.php" class="nav-link px-3 link-body-emphasis">Feedback</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- main -->
    <main id="main-section">
        <h2 id="sub-div-header">Dashboard</h2>

        <div class="row">
            <!-- Monthly Utility Usage Trends -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        Monthly Utility Usage Trends
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- IoT Device Status -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        IoT Device Status
                    </div>
                    <div class="card-body">
                        <canvas id="iotStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- User Consumption Pattern -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        Daily Usage Pattern
                    </div>
                    <div class="card-body">
                        <canvas id="consumptionPatternChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Outage Impact -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        Outage Impact Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="outageImpactChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- User Activity -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        User Login Activity
                    </div>
                    <div class="card-body">
                        <canvas id="userActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Feedback Analysis -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        Monthly Feedback Overview
                    </div>
                    <div class="card-body">
                        <canvas id="feedbackChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="text-center">
            <p class="mb-0">© 2024 CLIX. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- script -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/chart.js"></script>
    <script>
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        const iotData = <?php echo json_encode($iotData); ?>;
        const userPatternData = <?php echo json_encode($userPatternData); ?>;
        const outageData = <?php echo json_encode($outageData); ?>;
        const activityData = <?php echo json_encode($activityData); ?>;
        const feedbackData = <?php echo json_encode($feedbackData); ?>;
    </script>
    <script src="../js/admin-chart.js"></script>
    
</body>

</html>