<?php
global $conn;
session_start();
require_once './db-connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        $iot_id = filter_input(INPUT_POST, 'iot_id', FILTER_VALIDATE_INT);
        $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
        $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

        if (!$iot_id || !$latitude || !$longitude) {
            throw new Exception('Invalid input. Please provide a valid IoT ID and location.');
        }

        // Start a database transaction
        $conn->begin_transaction();

        // Check if IoT device exists in inactive_iot_table
        $check_inactive_stmt = $conn->prepare("SELECT _inactive_iot_id_ FROM inactive_iot_table WHERE _inactive_iot_id_ = ?");
        $check_inactive_stmt->bind_param("i", $iot_id);
        $check_inactive_stmt->execute();
        $check_inactive_result = $check_inactive_stmt->get_result();

        if ($check_inactive_result->num_rows === 0) {
            throw new Exception('IoT device not found in inactive devices or already in use.');
        }

        // Check if IoT device is already in pending_request_table
        $check_pending_stmt = $conn->prepare("SELECT _pending_request_id_ FROM pending_request_table 
                                          JOIN request_table ON pending_request_table._pending_request_id_ = request_table._request_id_ 
                                          WHERE request_table._iot_id_ = ?");
        $check_pending_stmt->bind_param("i", $iot_id);
        $check_pending_stmt->execute();
        $check_pending_result = $check_pending_stmt->get_result();

        if ($check_pending_result->num_rows > 0) {
            throw new Exception('IoT device already has a pending request.');
        }

        // Insert request into request_table
        $request_stmt = $conn->prepare("INSERT INTO request_table (_user_id_, _iot_id_, _latitude_, _longitude_, _request_time_) VALUES (?, ?, ?, ?, NOW())");
        $request_stmt->bind_param("iids", $_SESSION['_user_id_'], $iot_id, $latitude, $longitude);
        $request_stmt->execute();

        // Get the last inserted request ID
        $request_id = $conn->insert_id;

        // Insert request into pending_request_table
        $pending_stmt = $conn->prepare("INSERT INTO pending_request_table (_pending_request_id_) VALUES (?)");
        $pending_stmt->bind_param("i", $request_id);
        $pending_stmt->execute();

        // Commit transaction
        $conn->commit();

        $success_message = 'IoT device request submitted successfully!';
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Payment</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin-outage.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<?php
require_once './header.php';
?>

<!-- main -->
<main id="main-section" class="pb-3">
    <div class="card pb-3">
        <div class="card-body">
            <h2 id="sub-div-header">Request IoT Device</h2>

            <!-- Display error messages -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Display success messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form action="" method="POST" id="iot-request-form">
                <div class="mb-3">
                    <label for="iot-id" class="form-label">IoT ID</label>
                    <input type="text" class="form-control" id="iot-id" name="iot_id" required
                           placeholder="Enter IoT ID">
                </div>

                <div class="mb-3">
                    <input type="text" id="areaInput" class="form-control" placeholder="🔍 Search area"
                           autocomplete="off" style="max-width: 100%;">
                    <ul id="suggestions" class="list-group mt-2"></ul>
                </div>

                <div id="map" style="height: 300px; width: 100%;" class="mb-3"></div>

                <div class="mb-3 small">
                    <h5>Selected Location</h5>
                    <p class="mb-1">Area: <span id="areaName">N/A</span></p>
                    <p class="mb-1">Latitude: <span id="latitude">N/A</span></p>
                    <p class="mb-1">Longitude: <span id="longitude">N/A</span></p>
                </div>

                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-3 py-1">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
require_once './footer.php';
?>

<!-- scripts -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const map = L.map('map').setView([0, 0], 2);
        const iotIdInput = document.getElementById('iot-id');
        const latitudeInput = document.querySelector('input[name="latitude"]');
        const longitudeInput = document.querySelector('input[name="longitude"]');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let currentMarker;

        function addMarker(coords) {
            if (currentMarker) {
                currentMarker.setLatLng(coords);
            } else {
                currentMarker = L.marker(coords, {draggable: true}).addTo(map);
                currentMarker.bindPopup('You selected this location.').openPopup();
            }

            updateCoordinates(coords);

            currentMarker.on('dragend', () => {
                const {lat, lng} = currentMarker.getLatLng();
                updateCoordinates([lat, lng]);
            });
        }

        function updateCoordinates([lat, lng]) {
            document.getElementById('latitude').textContent = lat.toFixed(6);
            document.getElementById('longitude').textContent = lng.toFixed(6);

            // Update hidden input fields for form submission
            latitudeInput.value = lat.toFixed(6);
            longitudeInput.value = lng.toFixed(6);

            fetchAreaName(lat, lng);
            console.log(`Selected Coordinates: Latitude ${lat}, Longitude ${lng}`);
        }

        async function fetchAreaName(lat, lng) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                const data = await response.json();

                const areaName = data.display_name || 'Unknown location';
                document.getElementById('areaName').textContent = areaName;
                console.log(`Selected Area: ${areaName}`);
            } catch (error) {
                console.error('Error fetching area name:', error);
            }
        }

        document.getElementById('areaInput').addEventListener('input', async (event) => {
            const query = event.target.value;
            const suggestionsList = document.getElementById('suggestions');

            if (query.length < 3) {
                suggestionsList.innerHTML = '';
                return;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1`);
                const results = await response.json();

                suggestionsList.innerHTML = '';
                results.forEach((result) => {
                    const li = document.createElement('li');
                    li.textContent = result.display_name;
                    li.classList.add('list-group-item');
                    li.addEventListener('click', () => selectSuggestion(result));
                    suggestionsList.appendChild(li);
                });
            } catch (error) {
                console.error('Error fetching suggestions:', error);
            }
        });

        function selectSuggestion(result) {
            const coords = [parseFloat(result.lat), parseFloat(result.lon)];
            addMarker(coords);
            map.setView(coords, 15);
            document.getElementById('suggestions').innerHTML = '';
            document.getElementById('areaInput').value = result.display_name;
        }

        map.on('click', (e) => {
            const coords = [e.latlng.lat, e.latlng.lng];
            addMarker(coords);
        });

        // Form validation before submission
        document.getElementById('iot-request-form').addEventListener('submit', function (e) {
            const iotId = iotIdInput.value.trim();
            const latitude = latitudeInput.value;
            const longitude = longitudeInput.value;

            if (!iotId) {
                e.preventDefault();
                alert('Please enter an IoT Device ID');
                return;
            }

            if (!latitude || !longitude) {
                e.preventDefault();
                alert('Please select a location on the map');
                return;
            }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    map.setView([lat, lon], 12);

                    L.marker([lat, lon]).addTo(map)
                        .bindPopup("Your Current Location")
                        .openPopup();
                },
                function (error) {
                    console.warn("Geolocation failed: " + error.message);
                }
            );
        } else {
            console.warn("Geolocation is not supported by this browser.");
        }
    });
</script>

</body>

</html>
