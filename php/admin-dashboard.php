<?php
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
    <title>CLIX: Convenient Living & Integrated Experience</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

<!-- main -->
<?php
try {
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

<!-- footer -->
<?php
require_once './admin-footer.php';
?>

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
