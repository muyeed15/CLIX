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
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/chart.js"></script>
<script src="../js/chart.script.js"></script>
<script src="../js/usage-simulation.js"></script>

</body>
</html>
