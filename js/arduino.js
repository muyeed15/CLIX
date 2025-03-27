document.addEventListener('DOMContentLoaded', function() {
    const arduinoOutputBody = document.getElementById('arduino-output-body');

    function fetchArduinoOutput() {
        fetch('../data/arduino_last_output.txt?_=' + new Date().getTime())
            .then(response => response.text())
            .then(data => {
                // Parse the output
                const outputParts = data.split('|');
                const rssi = outputParts[0].replace('RSSI:', '').trim();
                const message = outputParts[1].replace('Message:', '').trim();

                // Create an object to store parsed values
                const parsedValues = {};
                message.split(',').forEach(part => {
                    const [key, value] = part.trim().split(':');
                    parsedValues[key.trim()] = value.trim();
                });

                // Update the table
                arduinoOutputBody.innerHTML = `
                    <tr>
                        <td>${rssi}</td>
                        <td>${parsedValues['Ampere']}</td>
                        <td>${parsedValues['Watt']}</td>
                        <td>${parsedValues['Watt-hour']}</td>
                    </tr>
                `;
            })
            .catch(error => {
                console.error('Error fetching Arduino output:', error);
                arduinoOutputBody.innerHTML = `
                    <tr>
                        <td colspan="4">Error fetching data</td>
                    </tr>
                `;
            });
    }

    fetchArduinoOutput();

    setInterval(fetchArduinoOutput, 500);
});
