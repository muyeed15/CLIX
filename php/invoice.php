<?php
global $conn;
session_start();
require_once './db-connection.php';

// Check if recharge_id is provided
if (!isset($_GET['recharge_id'])) {
    die('Recharge ID not provided');
}

$recharge_id = (int)$_GET['recharge_id'];

try {
    // Get recharge details with user and IoT information
    $query = "SELECT r.*, i._iot_label_, i._iot_id_, u._first_name_, u._last_name_, u._email_, u._phone_,
              u._current_address_, ut._cost_per_unit_,
              CASE 
                  WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                  WHEN w._water_id_ IS NOT NULL THEN 'Water'
                  WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
              END as utility_type
              FROM recharge_table r
              JOIN iot_table i ON r._iot_id_ = i._iot_id_
              JOIN user_table u ON r._user_id_ = u._user_id_
              JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
              LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
              LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
              LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
              WHERE r._recharge_id_ = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $recharge_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $invoice_data = mysqli_fetch_assoc($result);

    if (!$invoice_data) {
        die('Invoice not found');
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    die('Error generating invoice: ' . $e->getMessage());
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $recharge_id; ?></title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/invoice.css">

</head>

<!-- body -->

<body class="bg-light">
<div class="container my-5">
    <div class="invoice-box">
        <div class="invoice-header">
            <div class="row">
                <div class="col-6">
                    <h2>CLIX</h2>
                    <p>Convenient Living & Integrated Experience</p>
                </div>
                <div class="col-6 text-end">
                    <h3>INVOICE</h3>
                    <p>Invoice #: <?php echo $recharge_id; ?><br>
                        Date: <?php echo date('d/m/Y', strtotime($invoice_data['_recharge_time_'])); ?><br>
                        Time: <?php echo date('h:ia', strtotime($invoice_data['_recharge_time_'])); ?></p>
                </div>
            </div>
        </div>

        <div class="invoice-details">
            <div class="row">
                <div class="col-6">
                    <h5>Bill To:</h5>
                    <p>
                        <?php echo htmlspecialchars($invoice_data['_first_name_'] . ' ' . $invoice_data['_last_name_']); ?><br>
                        <?php echo htmlspecialchars($invoice_data['_email_']); ?><br>
                        <?php echo htmlspecialchars($invoice_data['_phone_']); ?><br>
                        <?php echo htmlspecialchars($invoice_data['_current_address_']); ?>
                    </p>
                </div>
                <div class="col-6">
                    <h5>Device Details:</h5>
                    <p>
                        ID: <?php echo htmlspecialchars($invoice_data['_iot_id_']); ?><br>
                        Label: <?php echo htmlspecialchars($invoice_data['_iot_label_']); ?><br>
                        Type: <?php echo htmlspecialchars($invoice_data['utility_type']); ?>
                    </p>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Rate</th>
                <th class="text-end">Amount</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo htmlspecialchars($invoice_data['utility_type']); ?> Recharge</td>
                <td class="text-end"><?php echo number_format($invoice_data['_cost_per_unit_'], 2); ?> tk/unit</td>
                <td class="text-end"><?php echo number_format($invoice_data['_recharge_amount_'], 2); ?> tk</td>
            </tr>
            <tr>
                <td colspan="2" class="text-end"><strong>Total Amount</strong></td>
                <td class="text-end"><strong><?php echo number_format($invoice_data['_recharge_amount_'], 2); ?> tk</strong></td>
            </tr>
            </tbody>
        </table>

        <div class="mt-4 text-center">
            <p><small>Thank you for using CLIX services!</small></p>
        </div>

        <div class="mt-4 text-center no-print">
            <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
            <button onclick="window.close()" class="btn btn-secondary ms-2">Close</button>
        </div>
    </div>
</div>

</body>

</html>