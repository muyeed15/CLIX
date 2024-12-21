<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_usage') {
        $deviceId = mysqli_real_escape_string($conn, $_POST['device_id']);
        $usageAmount = floatval($_POST['usage_amount']);
        $totalAmount = floatval($_POST['total_amount']);
        $balance = floatval($_POST['balance']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        mysqli_begin_transaction($conn);

        try {
            $userQuery = "SELECT _user_id_ FROM balance_table WHERE _iot_id_ = ?";
            $stmt = mysqli_prepare($conn, $userQuery);
            mysqli_stmt_bind_param($stmt, "i", $deviceId);
            mysqli_stmt_execute($stmt);
            $userResult = mysqli_stmt_get_result($stmt);
            $userData = mysqli_fetch_assoc($userResult);
            $userId = $userData['_user_id_'];

            $usageQuery = "INSERT INTO usage_table (_iot_id_, _user_id_, _usage_time_, _usage_amount_) 
                          VALUES (?, ?, NOW(), ?)";
            $stmt = mysqli_prepare($conn, $usageQuery);
            mysqli_stmt_bind_param($stmt, "iid", $deviceId, $userId, $usageAmount);
            mysqli_stmt_execute($stmt);

            $balanceQuery = "UPDATE balance_table SET _current_balance_ = ? WHERE _iot_id_ = ?";
            $stmt = mysqli_prepare($conn, $balanceQuery);
            mysqli_stmt_bind_param($stmt, "di", $balance, $deviceId);
            mysqli_stmt_execute($stmt);

            if ($balance <= 0) {
                $deleteActive = "DELETE FROM active_iot_table WHERE _active_iot_id_ = ?";
                $stmt = mysqli_prepare($conn, $deleteActive);
                mysqli_stmt_bind_param($stmt, "i", $deviceId);
                mysqli_stmt_execute($stmt);

                $insertUnpaid = "INSERT IGNORE INTO unpaid_iot_table (_unpaid_iot_id_) VALUES (?)";
                $stmt = mysqli_prepare($conn, $insertUnpaid);
                mysqli_stmt_bind_param($stmt, "i", $deviceId);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conn);
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

$utilityRatesQuery = "SELECT 
    'Gas' as type, ut._cost_per_unit_ as rate FROM utility_table ut 
    JOIN gas_table g ON ut._utility_id_ = g._gas_id_
    UNION 
    SELECT 'Water', ut._cost_per_unit_ FROM utility_table ut 
    JOIN water_table w ON ut._utility_id_ = w._water_id_
    UNION 
    SELECT 'Electricity', ut._cost_per_unit_ FROM utility_table ut 
    JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_";

$ratesResult = mysqli_query($conn, $utilityRatesQuery);
$utilityRates = [];
while ($rate = mysqli_fetch_assoc($ratesResult)) {
    $utilityRates[$rate['type']] = floatval($rate['rate']);
}
?>

<script>
    const UTILITY_RATES = <?php echo json_encode($utilityRates); ?>;

    const devicePatterns = new Map();

    function createDevicePattern() {
        return {
            baseRate: Math.random() * 0.5 + 0.1,
            burstChance: Math.random() * 0.15,
            burstMultiplier: Math.random() * 3 + 1.5,
            idleChance: Math.random() * 0.2
        };
    }

    function generateUsageIncrease(deviceType, pattern) {
        const roll = Math.random();

        if (roll < pattern.idleChance) {
            return 0;
        }

        let increase = pattern.baseRate * (0.5 + Math.random());

        if (roll > (1 - pattern.burstChance)) {
            increase *= pattern.burstMultiplier;
        }

        switch (deviceType) {
            case 'Gas':
                increase *= 0.4;
                break;
            case 'Water':
                increase *= 0.3;
                break;
            case 'Electricity':
                increase *= 0.5;
                break;
        }

        return increase;
    }

    async function updateDatabase(deviceId, currentUsage, totalUsage, newBalance, status) {
        const formData = new FormData();
        formData.append('action', 'update_usage');
        formData.append('device_id', deviceId);
        formData.append('usage_amount', currentUsage);
        formData.append('total_amount', totalUsage);
        formData.append('balance', newBalance);
        formData.append('status', status);

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (!result.success) {
                console.error('Failed to update database:', result.error);
            }
        } catch (error) {
            console.error('Error updating database:', error);
        }
    }

    function updateDisplay() {
        const rows = document.querySelectorAll('#iot-table tbody tr');

        rows.forEach(async (row) => {
            const statusCell = row.cells[6];
            const usageCell = row.cells[3];
            const totalUsageCell = row.cells[4];
            const balanceCell = row.cells[5];
            const deviceId = row.cells[1].textContent;

            if (statusCell.textContent.trim() === 'Active') {
                const deviceType = row.cells[0].querySelector('img').alt;
                if (!devicePatterns.has(deviceId)) {
                    devicePatterns.set(deviceId, createDevicePattern());
                }
                const pattern = devicePatterns.get(deviceId);

                let [currentValue, unit] = usageCell.textContent.trim().split(' ');
                let [totalValue] = totalUsageCell.textContent.trim().split(' ');
                let [balance] = balanceCell.textContent.trim().split(' ৳');

                currentValue = parseFloat(currentValue);
                totalValue = parseFloat(totalValue);
                balance = parseFloat(balance);

                const increase = generateUsageIncrease(deviceType, pattern);

                if (increase === 0) return;

                const rate = UTILITY_RATES[deviceType];
                const cost = increase * rate;
                const newBalance = (balance - cost).toFixed(2);
                const newCurrent = (currentValue + increase).toFixed(2);
                const newTotal = (totalValue + increase).toFixed(2);

                // Update display
                usageCell.textContent = `${newCurrent} ${unit}`;
                totalUsageCell.textContent = `${newTotal} ${unit}`;
                balanceCell.textContent = `${newBalance} ৳`;

                // Visual feedback
                usageCell.style.backgroundColor = '#f0f8ff';
                balanceCell.style.backgroundColor = '#fff0f0';

                setTimeout(() => {
                    usageCell.style.backgroundColor = 'transparent';
                    balanceCell.style.backgroundColor = 'transparent';
                }, 500);

                let newStatus = statusCell.textContent;
                if (parseFloat(newBalance) <= 0 && statusCell.textContent !== 'Unpaid') {
                    statusCell.textContent = 'Unpaid';
                    statusCell.style.color = '#f05959';
                    newStatus = 'Unpaid';
                }

                // Update database
                await updateDatabase(deviceId, newCurrent, newTotal, newBalance, newStatus);
            }
        });
    }

    // Initialize patterns and start updates
    function initialize() {
        const rows = document.querySelectorAll('#iot-table tbody tr');
        rows.forEach(row => {
            const deviceId = row.cells[1].textContent;
            devicePatterns.set(deviceId, createDevicePattern());
        });

        return setInterval(updateDisplay, 2000);
    }

    // Start the simulation when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        const updateInterval = initialize();

        window.addEventListener('beforeunload', () => {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    });
</script>
