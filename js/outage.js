document.addEventListener("DOMContentLoaded", function () {
    // Initialize map with initial view of the world
    const map = L.map('map').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const UTILITY_COLORS = {
        'Gas': '#e44d4d',
        'Water': '#3d81ff',
        'Electricity': '#2ea38a'
    };

    const IMPACT_SETTINGS = {
        'HIGH': { opacity: 0.8, weight: 3 },
        'MEDIUM': { opacity: 0.5, weight: 2 },
        'LOW': { opacity: 0.3, weight: 1 }
    };

    let outageMarkers = [];
    let currentMarker;
    let searchInput = document.getElementById('client-search');
    let searchForm = document.getElementById('searchForm');

    // Clear all outage markers from the map
    function clearOutageMarkers() {
        outageMarkers.forEach(marker => map.removeLayer(marker));
        outageMarkers = [];
    }

    // Create an outage marker with proper styling and popup
    function createOutageMarker(outage) {
        const {
            _latitude_,
            _longitude_,
            _range_,
            _area_,
            _date_start_,
            _time_start_,
            _date_end_,
            _time_end_,
            _type_,
            _impact_level_
        } = outage;

        const baseColor = UTILITY_COLORS[_type_] || 'gray';
        const impact = IMPACT_SETTINGS[_impact_level_] || { opacity: 0.5, weight: 1 };

        const circle = L.circle([_latitude_, _longitude_], {
            radius: parseFloat(_range_),
            color: baseColor,
            fillColor: baseColor,
            fillOpacity: impact.opacity,
            weight: impact.weight
        });

        circle.bindPopup(`
            <strong>Outage:</strong> ${_area_} <br>
            <strong>Type:</strong> ${_type_} <br>
            <strong>Impact Level:</strong> ${_impact_level_ || 'Unknown'} <br>
            <strong>Start:</strong> ${_date_start_} ${_time_start_} <br>
            <strong>End:</strong> ${_date_end_} ${_time_end_}
        `);

        return circle;
    }

    // Fetch and display all outages on the map
    async function fetchAndDisplayOutages() {
        try {
            const response = await fetch('admin-outage-fetch.php');
            const outages = await response.json();

            clearOutageMarkers();

            outages.forEach(outage => {
                const marker = createOutageMarker(outage);
                marker.addTo(map);
                outageMarkers.push(marker);
            });

            if (outageMarkers.length > 0) {
                const group = L.featureGroup(outageMarkers);
                map.fitBounds(group.getBounds(), { padding: [50, 50] });
            }
        } catch (error) {
            console.error("Error fetching outage data:", error);
        }
    }

    // Fetch area name from coordinates
    async function fetchAreaName(lat, lng) {
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
            const data = await response.json();
            return data.display_name || 'Unknown location';
        } catch (error) {
            console.error("Error fetching area name:", error);
            return 'Unknown location';
        }
    }

    // Update form fields and displays with location information
    function updateFormFields(area, lat, lng) {
        // Update hidden inputs
        document.getElementById('areaNameInput').value = area;
        document.getElementById('latitudeInput').value = lat;
        document.getElementById('longitudeInput').value = lng;

        // Update display spans
        document.getElementById('areaName').textContent = area;
        document.getElementById('latitude').textContent = lat;
        document.getElementById('longitude').textContent = lng;
    }

    // Add or update the current marker
    async function addMarker(coords) {
        if (currentMarker) {
            map.removeLayer(currentMarker);
        }

        currentMarker = L.marker(coords, { draggable: true }).addTo(map);
        const areaName = await fetchAreaName(coords[0], coords[1]);

        updateFormFields(areaName, coords[0].toFixed(6), coords[1].toFixed(6));
        currentMarker.bindPopup('Selected: ' + areaName).openPopup();

        // Handle marker drag
        currentMarker.on('dragend', async () => {
            const pos = currentMarker.getLatLng();
            const newAreaName = await fetchAreaName(pos.lat, pos.lng);
            updateFormFields(newAreaName, pos.lat.toFixed(6), pos.lng.toFixed(6));
            currentMarker.bindPopup('Selected: ' + newAreaName).openPopup();
        });
    }

    // Area search functionality
    const areaInput = document.getElementById('areaInput');
    const suggestionsList = document.getElementById('suggestions');

    if (areaInput) {
        areaInput.addEventListener('input', async (event) => {
            const query = event.target.value.trim();

            if (query.length < 3) {
                suggestionsList.innerHTML = '';
                return;
            }

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1`
                );
                const results = await response.json();

                suggestionsList.innerHTML = '';
                results.forEach((result) => {
                    const li = document.createElement('li');
                    li.textContent = result.display_name;
                    li.classList.add('list-group-item');
                    li.addEventListener('click', () => {
                        const coords = [parseFloat(result.lat), parseFloat(result.lon)];
                        addMarker(coords);
                        map.setView(coords, 15);
                        suggestionsList.innerHTML = '';
                        areaInput.value = result.display_name;
                    });
                    suggestionsList.appendChild(li);
                });
            } catch (error) {
                console.error("Error fetching suggestions:", error);
            }
        });
    }

    // Map click handler
    map.on('click', (e) => {
        addMarker([e.latlng.lat, e.latlng.lng]);
    });

    // Form validation and submission
    const outageForm = document.getElementById('outageForm');
    if (outageForm) {
        outageForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const area = document.getElementById('areaNameInput').value;
            const lat = document.getElementById('latitudeInput').value;
            const lng = document.getElementById('longitudeInput').value;
            const radius = document.getElementById('radiusInput').value;
            const startDate = document.getElementById('startDate').value;
            const startTime = document.getElementById('startTime').value;
            const endDate = document.getElementById('endDate').value;
            const endTime = document.getElementById('endTime').value;

            if (!area || !lat || !lng || !radius || !startDate || !startTime || !endDate || !endTime) {
                alert('Please fill in all required fields including selecting a location on the map');
                return;
            }

            const startDateTime = new Date(startDate + ' ' + startTime);
            const endDateTime = new Date(endDate + ' ' + endTime);

            if (endDateTime <= startDateTime) {
                alert('End time must be after start time');
                return;
            }

            outageForm.submit();
        });
    }

    // Delete outage functionality
    document.querySelectorAll('.delete-outage').forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this outage?')) {
                const outageId = this.dataset.id;
                try {
                    const response = await fetch('delete_outage.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `outage_id=${outageId}`
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.closest('tr').remove();
                        // Refresh outages on map
                        fetchAndDisplayOutages();
                    } else {
                        alert('Error deleting outage');
                    }
                } catch (error) {
                    console.error("Error deleting outage:", error);
                    alert('Error deleting outage');
                }
            }
        });
    });

    // Search functionality
    if (searchInput && searchForm) {
        searchInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchForm.submit();
            }
        });
    }

    // Handle search parameters and scrolling
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

    // Initialize geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                const coords = [position.coords.latitude, position.coords.longitude];
                map.setView(coords, 12);
                L.marker(coords).addTo(map)
                    .bindPopup("You are here!")
                    .openPopup();
            },
            function (error) {
                console.error("Geolocation failed:", error.message);
                fetchAndDisplayOutages();
            }
        );
    } else {
        console.warn("Geolocation not supported");
        fetchAndDisplayOutages();
    }

    // Load initial outages
    fetchAndDisplayOutages();
});