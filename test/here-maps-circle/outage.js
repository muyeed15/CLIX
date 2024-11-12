document.addEventListener("DOMContentLoaded", function () {
    // Initialize map with a default location in case geolocation is blocked
    var map = L.map('map').setView([0, 0], 2);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Check if geolocation is available
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;

                // Set map view to user's location
                map.setView([lat, lon], 12);

                // Add circle overlay at user's location
                L.circle([lat, lon], {
                    radius: 500,  // radius in meters
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.5
                }).addTo(map);

                // Optionally add a marker at the user's location
                L.marker([lat, lon]).addTo(map)
                    .bindPopup("You are here!")
                    .openPopup();
            },
            function (error) {
                alert("Geolocation failed: " + error.message);
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
});
