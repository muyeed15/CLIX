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

    async function fetchOutages() {
        try {
            const response = await fetch('admin-outage-fetch.php');
            const outages = await response.json();

            outages.forEach(outage => {
                const {
                    _latitude_,
                    _longitude_,
                    _range_,
                    _area_,
                    _date_start_,
                    _time_start_,
                    _date_end_,
                    _time_end_,
                    _type_
                } = outage;

                let circleColor;
                switch (_type_) {
                    case 'Gas':
                        circleColor = '#e44d4d';
                        break;
                    case 'Water':
                        circleColor = '#3d81ff';
                        break;
                    case 'Electricity':
                        circleColor = '#2ea38a';
                        break;
                    default:
                        circleColor = 'gray';
                }

                L.circle([_latitude_, _longitude_], {
                    radius: parseFloat(_range_),
                    color: circleColor,
                    fillColor: circleColor,
                    fillOpacity: 0.5
                }).addTo(map).bindPopup(`
                <strong>Outage:</strong> ${_area_} <br>
                <strong>Type:</strong> ${_type_} <br>
                <strong>Start:</strong> ${_date_start_} ${_time_start_} <br>
                <strong>End:</strong> ${_date_end_} ${_time_end_}
            `);
            });
        } catch (error) {
            console.error("Error fetching outage data:", error);
        }
    }

    fetchOutages();

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

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('client-search');
    const searchForm = document.getElementById('searchForm');

    if (searchInput && searchForm) {
        searchInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchForm.submit();
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);

    const searchParam = urlParams.get('search');
    if (searchParam && searchInput) {
        searchInput.value = decodeURIComponent(searchParam);
    }

    if (urlParams.has('search') || urlParams.has('page')) {
        const tableSection = document.getElementById('table-section');
        if (tableSection) {
            const tablePosition = tableSection.getBoundingClientRect().top + window.pageYOffset;

            window.scrollTo({
                top: tablePosition - 100,
                behavior: 'smooth'
            });
        }
    }
});
