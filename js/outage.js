document.addEventListener("DOMContentLoaded", function () {
    const map = L.map('map').setView([0, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let currentMarker;

    function addMarker(coords) {
        if (currentMarker) {
            currentMarker.setLatLng(coords);
        } else {
            currentMarker = L.marker(coords, { draggable: true }).addTo(map);
            currentMarker.bindPopup('You selected this location.').openPopup();
        }

        updateCoordinates(coords);

        currentMarker.on('dragend', () => {
            const { lat, lng } = currentMarker.getLatLng();
            updateCoordinates([lat, lng]);
        });
    }

    function updateCoordinates([lat, lng]) {
        document.getElementById('latitude').textContent = lat.toFixed(6);
        document.getElementById('longitude').textContent = lng.toFixed(6);
        fetchAreaName(lat, lng);
        console.log(`Selected Coordinates: Latitude ${lat}, Longitude ${lng}`);
    }

    async function fetchAreaName(lat, lng) {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
        const data = await response.json();

        const areaName = data.display_name || 'Unknown location';
        document.getElementById('areaName').textContent = areaName;
        console.log(`Selected Area: ${areaName}`);
    }

    document.getElementById('areaInput').addEventListener('input', async (event) => {
        const query = event.target.value;
        const suggestionsList = document.getElementById('suggestions');

        if (query.length < 3) {
            suggestionsList.innerHTML = '';
            return;
        }

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

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                map.setView([lat, lon], 12);

                // Add outage circles
                const outageData = [
                    { coords: [23.7800, 90.3800], radius: 500, color: 'red', popup: "Gas Outage" },
                    { coords: [23.7950, 90.4100], radius: 300, color: 'red', popup: "Gas Outage" },
                    { coords: [23.8150, 90.3600], radius: 450, color: 'red', popup: "Gas Outage" },
                    { coords: [23.8350, 90.4400], radius: 600, color: 'red', popup: "Gas Outage" },
                    { coords: [23.8500, 90.4000], radius: 350, color: 'red', popup: "Gas Outage" },
                    { coords: [23.8200, 90.4500], radius: 400, color: 'green', popup: "Electricity Outage" },
                    { coords: [23.8450, 90.4200], radius: 500, color: 'green', popup: "Electricity Outage" },
                    { coords: [23.7650, 90.3900], radius: 300, color: 'green', popup: "Electricity Outage" },
                    { coords: [23.8400, 90.4700], radius: 550, color: 'green', popup: "Electricity Outage" },
                    { coords: [23.7900, 90.4300], radius: 250, color: 'green', popup: "Electricity Outage" },
                    { coords: [23.8100, 90.3800], radius: 350, color: 'blue', popup: "Water Outage" },
                    { coords: [23.8600, 90.3950], radius: 450, color: 'blue', popup: "Water Outage" },
                    { coords: [23.7750, 90.4050], radius: 400, color: 'blue', popup: "Water Outage" },
                    { coords: [23.8500, 90.4550], radius: 600, color: 'blue', popup: "Water Outage" },
                    { coords: [23.8300, 90.3850], radius: 200, color: 'blue', popup: "Water Outage" },
                ];

                outageData.forEach(({ coords, radius, color, popup }) => {
                    L.circle(coords, {
                        radius,
                        color,
                        fillColor: color,
                        fillOpacity: 0.5
                    }).addTo(map).bindPopup(popup);
                });

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