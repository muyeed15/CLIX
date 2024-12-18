let UTILITY_RATES = {};
const devicePatterns = new Map();

async function fetchUtilityRates() {
    try {
        const response = await fetch('get-utility-rates.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Failed to retrieve utility rates');
        }

        data.rates.forEach(rate => {
            UTILITY_RATES[rate.utility_type] = rate._cost_per_unit_;
        });

        return true;
    } catch (error) {
        console.error('Error fetching utility rates:', error);
        return false;
    }
}

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
            increase *= 2.0;
            break;
        case 'Electricity':
            increase *= 0.6;
            break;
    }

    return increase;
}

async function updateUsage() {
    if (Object.keys(UTILITY_RATES).length === 0) {
        console.warn('Utility rates not loaded yet, skipping update');
        return;
    }

    const rows = document.querySelectorAll('#iot-table tbody tr');

    rows.forEach(async row => {
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
            if (!rate) {
                console.error(`No rate found for utility type: ${deviceType}`);
                return;
            }
            const cost = increase * rate;
            const newBalance = (balance - cost).toFixed(2);
            const newCurrent = (currentValue + increase).toFixed(2);
            const newTotal = (totalValue + increase).toFixed(2);

            usageCell.textContent = `${newCurrent} ${unit}`;
            totalUsageCell.textContent = `${newTotal} ${unit}`;
            balanceCell.textContent = `${newBalance} ৳`;

            usageCell.style.backgroundColor = '#f0f8ff';
            balanceCell.style.backgroundColor = '#fff0f0';

            setTimeout(() => {
                usageCell.style.backgroundColor = 'transparent';
                balanceCell.style.backgroundColor = 'transparent';
            }, 500);

            if (parseFloat(newBalance) <= 0 && statusCell.textContent !== 'Unpaid') {
                statusCell.textContent = 'Unpaid';
                statusCell.style.color = '#f05959';
            }

            try {
                const response = await fetch('update-usage.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        iot_id: parseInt(deviceId),
                        usage_amount: increase,
                        utility_type: deviceType
                    }),
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                if (data.success && data.data) {
                    const { current_usage, total_usage, balance, status } = data.data;
                    if (current_usage !== parseFloat(newCurrent) ||
                        total_usage !== parseFloat(newTotal) ||
                        balance !== parseFloat(newBalance) ||
                        status !== statusCell.textContent) {

                        usageCell.textContent = `${current_usage.toFixed(2)} ${unit}`;
                        totalUsageCell.textContent = `${total_usage.toFixed(2)} ${unit}`;
                        balanceCell.textContent = `${balance.toFixed(2)} ৳`;

                        if (status !== statusCell.textContent) {
                            statusCell.textContent = status;
                            if (status === 'Unpaid') {
                                statusCell.style.color = '#f05959';
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error syncing with database:', error);
            }
        }
    });
}

async function initialize() {
    const ratesLoaded = await fetchUtilityRates();
    if (!ratesLoaded) {
        console.error('Failed to load utility rates. System may not function correctly.');
        return;
    }

    const rows = document.querySelectorAll('#iot-table tbody tr');
    rows.forEach(row => {
        const deviceId = row.cells[1].textContent;
        devicePatterns.set(deviceId, createDevicePattern());
    });

    return setInterval(updateUsage, 2000);
}

document.addEventListener('DOMContentLoaded', () => {
    const updateInterval = initialize();

    window.addEventListener('beforeunload', () => {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
});