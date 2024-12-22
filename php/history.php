<?php
global $conn;
session_start();
require_once './db-connection.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/animation.css">
    <link rel="stylesheet" href="../css/history.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php require_once './header.php'; ?>

<!-- main -->
<?php
try {
    // Pagination
    $items_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    $count_query = "SELECT COUNT(*) as total FROM recharge_table WHERE _user_id_ = ?";
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $total_result = mysqli_stmt_get_result($stmt);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_recharges = $total_row['total'];
    $total_pages = ceil($total_recharges / $items_per_page);

    // History
    $query = "SELECT r.*, i._iot_label_, i._iot_id_, 
            CASE 
                WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                WHEN w._water_id_ IS NOT NULL THEN 'Water'
                WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
            END as utility_type,
            CASE 
                WHEN g._gas_id_ IS NOT NULL THEN '../img/gas-costs-svgrepo-com.svg'
                WHEN w._water_id_ IS NOT NULL THEN '../img/water-fee-svgrepo-com.svg'
                WHEN e._electricity_id_ IS NOT NULL THEN '../img/hydropower-coal-svgrepo-com.svg'
            END as utility_icon
            FROM recharge_table r
            JOIN iot_table i ON r._iot_id_ = i._iot_id_
            JOIN utility_table u ON i._utility_id_ = u._utility_id_
            LEFT JOIN gas_table g ON u._utility_id_ = g._gas_id_
            LEFT JOIN water_table w ON u._utility_id_ = w._water_id_
            LEFT JOIN electricity_table e ON u._utility_id_ = e._electricity_id_
            WHERE r._user_id_ = ?
            ORDER BY r._recharge_time_ DESC
            LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $items_per_page, $offset);
    mysqli_stmt_execute($stmt);
    $recharges = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}
?>

<main id="main-section">
    <div>
        <h2 id="sub-div-header">Recharge History</h2>
        <table class="table table-borderless" id="iot-table">
            <thead id="iot-thead">
            <tr>
                <th scope="col" width="1vw">Device</th>
                <th scope="col">ID</th>
                <th scope="col">Label</th>
                <th scope="col">Type</th>
                <th scope="col">Amount</th>
                <th scope="col">Time</th>
                <th scope="col">Date</th>
                <th scope="col">Invoice</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($recharges)): ?>
                <tr>
                    <td class="d-flex justify-content-center">
                        <img class="utility-svg" src="<?php echo htmlspecialchars($row['utility_icon']); ?>"
                             alt="<?php echo htmlspecialchars($row['utility_type']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($row['_iot_id_']); ?></td>
                    <td><?php echo htmlspecialchars($row['_iot_label_']); ?></td>
                    <td><?php echo htmlspecialchars($row['utility_type']); ?></td>
                    <td><?php echo number_format($row['_recharge_amount_'], 2) . 'tk'; ?></td>
                    <td><?php echo date('h:ia', strtotime($row['_recharge_time_'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['_recharge_time_'])); ?></td>
                    <td>
                        <a href="invoice.php?recharge_id=<?php echo htmlspecialchars($row['_recharge_id_']); ?>"
                           class="btn btn-sm btn-primary"
                           target="_blank">
                            View Invoice
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- pagination -->
    <?php require_once './pagination.php'; ?>
</main>

<!-- footer -->
<?php require_once './footer.php'; ?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>

</body>
</html>
