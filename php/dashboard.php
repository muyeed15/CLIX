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
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<?php

try {
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
            END AS _status_
        FROM 
            iot_table i
            INNER JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
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
            LEFT JOIN unpaid_iot_table u ON i._iot_id_ = u._unpaid_iot_id_
            INNER JOIN balance_table user_iot ON i._iot_id_ = user_iot._iot_id_
        WHERE 
            user_iot._user_id_ = ? AND 
            i._iot_id_ NOT IN (SELECT _inactive_iot_id_ FROM inactive_iot_table) AND
            (
                b._current_balance_ > 0 AND a._active_iot_id_ IS NOT NULL OR 
                b._current_balance_ <= 0 OR u._unpaid_iot_id_ IS NOT NULL
            )
        GROUP BY 
            _type_, 
            i._iot_id_, 
            i._iot_label_,
            u_current._usage_amount_,
            b._current_balance_,
            a._active_iot_id_,
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
                <th>Settings</th>
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
                            switch ($row['_type_']) {
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
                    <td><?php echo !empty($row['_label_']) ? htmlspecialchars($row['_label_']) : 'N/A'; ?></td>
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
                        <a href="iot-settings.php?iot_id=<?= htmlspecialchars($row['_iot_id_']); ?>"
                           class="btn btn-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                 class="bi bi-gear" viewBox="0 0 16 16">
                                <path
                                    d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                <path
                                    d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                            </svg>
                        </a>
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
    <?php
    try {
        // Request IoT Devices Query
        $requestQuery = "SELECT 
            r._request_id_,
            i._iot_id_,
            i._iot_label_ AS _label_,
            CASE 
                WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
                WHEN w._water_id_ IS NOT NULL THEN 'Water'
            END AS _type_,
            r._request_time_ AS _request_time_,
            CASE 
                WHEN pr._pending_request_id_ IS NOT NULL THEN 'Pending'
                WHEN dr._declined_request_id_ IS NOT NULL THEN 'Declined'
                ELSE 'Unknown'
            END AS _status_
        FROM 
            request_table r
            INNER JOIN iot_table i ON r._iot_id_ = i._iot_id_
            INNER JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
            LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
            LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
            LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
            LEFT JOIN pending_request_table pr ON r._request_id_ = pr._pending_request_id_
            LEFT JOIN declined_request_table dr ON r._request_id_ = dr._declined_request_id_
        WHERE 
            r._user_id_ = ?
            AND pr._pending_request_id_ IS NOT NULL
        ORDER BY 
            r._request_time_ DESC LIMIT 5;";

        $stmt = mysqli_prepare($conn, $requestQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $requestSummary = mysqli_stmt_get_result($stmt);

        // Check if there are any pending requests
        $pendingRequestCount = mysqli_num_rows($requestSummary);

        mysqli_stmt_close($stmt);

    } catch (Exception $e) {
        echo "Error fetching request data: " . $e->getMessage();
    }
    ?>

    <?php if ($pendingRequestCount > 0): ?>
        <div>
            <h2 id="sub-div-header">Requested IoT Devices</h2>
            <table class="table table-borderless" id="iot-table" style="color: #282828;">
                <thead id="request-iot-thead">
                <tr>
                    <th>Device</th>
                    <th>Request ID</th>
                    <th>Device ID</th>
                    <th>Request Time</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($requestSummary, 0);
                while ($row = mysqli_fetch_assoc($requestSummary)) : ?>
                    <tr>
                        <td>
                            <?php
                            $utilityIcon = '';
                            $outageType = '';
                            if (isset($row['_type_'])) {
                                switch ($row['_type_']) {
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
                        <td><?= htmlspecialchars($row['_request_id_']); ?></td>
                        <td><?= htmlspecialchars($row['_iot_id_']); ?></td>
                        <td><?= htmlspecialchars($row['_request_time_']); ?></td>
                        <td style="color: #f0ad4e;">
                            <?= htmlspecialchars($row['_status_']); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    </div>
    
    <!-- LoRa -->
    <div id="arduino-output-container">
        <h2 id="sub-div-header">LoRa Meter</h2>
        <table class="table table-borderless" style="color: #282828;">
            <thead>
                <tr>
                    <th>RSSI</th>
                    <th>Ampere</th>
                    <th>Watt</th>
                    <th>Watt-hour</th>
                </tr>
            </thead>
            <tbody id="arduino-output-body">
                <!-- This will be populated by JavaScript -->
            </tbody>
        </table>
    </div>

</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/chart.js"></script>
<script src="../js/arduino.js"></script>
<?php
require_once './chart.php';
require_once './live.php';
?>
</body>
</html>
