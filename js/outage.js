document.addEventListener("DOMContentLoaded", function () {
    var map = L.map('map').setView([0, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;

                map.setView([lat, lon], 12);

                L.circle([23.7800, 90.3800], { radius: 500, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");
                L.circle([23.7950, 90.4100], { radius: 300, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");
                L.circle([23.8150, 90.3600], { radius: 450, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");
                L.circle([23.8350, 90.4400], { radius: 600, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");
                L.circle([23.8500, 90.4000], { radius: 350, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");

                L.circle([23.8200, 90.4500], { radius: 400, color: 'green', fillColor: 'green', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");
                L.circle([23.8450, 90.4200], { radius: 500, color: 'green', fillColor: 'green', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");
                L.circle([23.7650, 90.3900], { radius: 300, color: 'green', fillColor: 'green', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");
                L.circle([23.8400, 90.4700], { radius: 550, color: 'green', fillColor: 'green', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");
                L.circle([23.7900, 90.4300], { radius: 250, color: 'green', fillColor: 'green', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");

                L.circle([23.8100, 90.3800], { radius: 350, color: 'blue', fillColor: 'blue', fillOpacity: 0.5 }).addTo(map).bindPopup("Water Outage");
                L.circle([23.8600, 90.3950], { radius: 450, color: 'blue', fillColor: 'blue', fillOpacity: 0.5 }).addTo(map).bindPopup("Water Outage");
                L.circle([23.7750, 90.4050], { radius: 400, color: 'blue', fillColor: 'blue', fillOpacity: 0.5 }).addTo(map).bindPopup("Water Outage");
                L.circle([23.8500, 90.4550], { radius: 600, color: 'blue', fillColor: 'blue', fillOpacity: 0.5 }).addTo(map).bindPopup("Water Outage");
                L.circle([23.8300, 90.3850], { radius: 200, color: 'blue', fillColor: 'blue', fillOpacity: 0.5 }).addTo(map).bindPopup("Water Outage");

                L.circle([23.7950, 90.4600], { radius: 500, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");
                L.circle([23.8500, 90.4200], { radius: 600, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");
                L.circle([23.7900, 90.4450], { radius: 450, color: 'blue', fillColor: 'blue', fillOpacity: 0.5 }).addTo(map).bindPopup("Water Outage");
                L.circle([23.8150, 90.4100], { radius: 400, color: 'red', fillColor: 'red', fillOpacity: 0.5 }).addTo(map).bindPopup("Gas Outage");
                L.circle([23.7850, 90.3950], { radius: 300, color: 'green', fillColor: 'green', fillOpacity: 0.5 }).addTo(map).bindPopup("Electricity Outage");

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
