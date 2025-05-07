document.addEventListener('DOMContentLoaded', function() {
    const arduinoOutputBody = document.getElementById('arduino-output-body');

    function fetchArduinoOutput() {
        fetch('../data/arduino_last_output.txt?_=' + new Date().getTime())
            .then(response => response.text())
            .then(data => {
                // Parse the output
                const parts = data.split('|');
                if (parts.length < 2) {
                    throw new Error('Invalid data format');
                }

                // Extract RSSI from the first part
                const rssi = parts[0].replace('RSSI:', '').trim();
                
                // Extract message data from the second part
                const messageData = parts[1].replace('Message:', '').trim();
                
                // Parse the individual readings
                const readings = {};
                const valuePairs = messageData.split(',');
                
                valuePairs.forEach(pair => {
                    // Use regex to extract label and value
                    const match = pair.match(/([A-Za-z\s()]+):\s*([-\d.]+)/);
                    if (match) {
                        const label = match[1].trim();
                        const value = match[2].trim();
                        readings[label] = value;
                    }
                });

                // Determine values to display (with fallbacks if any value is missing)
                const current = readings['Current (A)'] || '0.00';
                const voltage = readings['Voltage (V)'] || '0.00';
                const power = readings['Power (W)'] || '0.00';
                
                // Calculate Watt-hour if not present (for backward compatibility)
                // This assumes watt-hour is cumulative and should be handled by the backend
                const wattHour = readings['Watt-hour'] || '0.00';

                // Update the table
                arduinoOutputBody.innerHTML = `
                    <tr>
                        <td>${rssi} dBm</td>
                        <td>${current} A</td>
                        <td>${power} W</td>
                        <td>${wattHour} Wh</td>
                    </tr>
                `;
            })
            .catch(error => {
                console.error('Error fetching Arduino output:', error);
                arduinoOutputBody.innerHTML = `
                    <tr>
                        <td colspan="4">Error fetching data: ${error.message}</td>
                    </tr>
                `;
            });
    }

    // Initial fetch
    fetchArduinoOutput();

    // Refresh every 500ms
    setInterval(fetchArduinoOutput, 500);
});