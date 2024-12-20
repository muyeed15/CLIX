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
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<main id="main-section">
    <div class="container">
        <h2 id="sub-div-header">Settings</h2 id="sub-div-header">

        <!-- Notification Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Notification Preferences</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Push Notifications</h6>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="billReminders" checked>
                        <label class="form-check-label" for="billReminders">Bill Payment Reminders</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="outageAlerts" checked>
                        <label class="form-check-label" for="outageAlerts">Outage Alerts</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="consumptionAlerts" checked>
                        <label class="form-check-label" for="consumptionAlerts">High Consumption Alerts</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="maintenanceTips">
                        <label class="form-check-label" for="maintenanceTips">Maintenance Tips</label>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Alert Timing</h6>
                    <div class="mb-2">
                        <label class="form-label">Outage Notification Lead Time</label>
                        <select class="form-select">
                            <option value="1">1 hour before (Default)</option>
                            <option value="2">2 hours before</option>
                            <option value="3">3 hours before</option>
                            <option value="4">4 hours before</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Auto-Payment</h6>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="autoPayElectricity">
                        <label class="form-check-label" for="autoPayElectricity">Enable Auto-pay for Electricity
                            Bills</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="autoPayWater">
                        <label class="form-check-label" for="autoPayWater">Enable Auto-pay for Water Bills</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="autoPayGas">
                        <label class="form-check-label" for="autoPayGas">Enable Auto-pay for Gas Bills</label>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Payment Methods</h6>
                    <button class="btn btn-outline-primary mb-2">Add New Payment Method</button>
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Visa ending in 4242</h6>
                                <small class="text-muted">Expires 12/24</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">bKash Account</h6>
                                <small class="text-muted">+880 1711-xxxxxx</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Map Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="showOutageZones" checked>
                        <label class="form-check-label" for="showOutageZones">Show Outage Zones on Map</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Default Map View</label>
                        <select class="form-select">
                            <option value="street">Street View</option>
                            <option value="satellite">Satellite View</option>
                            <option value="hybrid">Hybrid View</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="text-end pb-4">
            <button class="btn btn-primary">Save Changes</button>
        </div>
    </div>
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
