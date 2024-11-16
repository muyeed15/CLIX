<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin-outage.css">
</head>

<body>
    <!-- Header -->
    <header class="border-bottom" id="header-section">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <a href="#">
                    <img class="py-2" src="../img/CLIX.svg" id="header-logo">
                </a>

                <ul class="nav small py-2">
                    <li><a href="./admin-dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                    <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                    <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                    <li><a href="./admin-login-season.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">User</a></li>
                </ul>

                <div class="d-flex py-2">
                    <div class="dropdown text-end" id="notification-icon">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                class="bi bi-bell" viewBox="0 0 16 16">
                                <path
                                    d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item small" href="#">Your electricity bill is due tomorrow.</a></li>
                            <li><a class="dropdown-item small" href="#">Gas service will be disrupted from 10:00 AM to 1:00 PM.</a></li>
                            <li><a class="dropdown-item small" href="#">Your water usage is higher than usual today.</a></li>
                            <li><a class="dropdown-item small" href="#">Check your gas connections for leaks.</a></li>
                            <li><a class="dropdown-item small" href="#">There may be low water pressure today.</a></li>
                            <li><a class="dropdown-item small" href="#">Reduce usage during peak hours.</a></li>
                            <li><a class="dropdown-item small" href="#">Power outage expected from 3:00 PM to 5:00 PM.</a></li>
                            <li><a class="dropdown-item small" href="#">Your gas bill is due in 3 days.</a></li>
                            <li><a class="dropdown-item small" href="#">Water supply will be disrupted tomorrow.</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../img/LinkedIn_1x1_1000px.jpg" alt="mdo" width="32" height="32" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="#">Profile</a></li>
                            <li><a class="dropdown-item small" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="#">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Section -->
    <main id="main-section">
        <div class="d-flex justify-content-between align-items-start">
            <div class="order-1 w-50 pe-2 pt-3 pb-2">
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 id="sub-div-header">IoT Control</h2>
                        <form id="create-iot-form">
                            <div class="mb-3">
                                <label for="iot-type" class="form-label">Type</label>
                                <select class="form-select" id="iot-type">
                                    <option value="Electricity">Electricity</option>
                                    <option value="Gas">Gas</option>
                                    <option value="Water">Water</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="iot-id" class="form-label">IoT ID</label>
                                <input type="text" class="form-control" id="iot-id" placeholder="Enter IoT ID">
                            </div>
                            <div class="mb-3">
                                <label for="iot-id" class="form-label">User NID</label>
                                <input type="text" class="form-control" id="iot-id" placeholder="Enter user NID">
                            </div>
                            <div class="mb-3">
                                <label for="iot-label" class="form-label">Label</label>
                                <input type="text" class="form-control" id="iot-label" placeholder="Enter label">
                            </div>
                            <div class="mb-3">
                                <label for="iot-latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control" id="iot-latitude" placeholder="Enter latitude">
                            </div>
                            <div class="mb-3">
                                <label for="iot-longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control" id="iot-longitude" placeholder="Enter longitude">
                            </div>
                            <div class="mb-3">
                                <label for="iot-cost" class="form-label">Cost per unit</label>
                                <input type="text" class="form-control" id="iot-cost" placeholder="Enter cost per unit">
                            </div>
                            <div class="mb-3">
                                <label for="payment-status-type" class="form-label">Payment Status</label>
                                <select class="form-select" id="payment-status-type">
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid">Unpaid</option>
                                    <option value="Banned">Banned</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-3 py-1">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="order-2 ms-auto w-50 ps-2 pt-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" id="areaInput" class="form-control" placeholder="🔍 Search area" autocomplete="off" style="max-width: 100%;">
                            <ul id="suggestions" class="list-group mt-2"></ul>
                        </div>
                        <div id="map" style="height: 300px; width: 100%;"></div>
                        <div class="d-flex justify-content-center mt-3">
                            <div class="px-3" id="map-gas">■ Gas</div>
                            <div class="px-3" id="map-water">■ Water</div>
                            <div class="px-3" id="map-electricity">■ Electricity</div>
                        </div>
                        <div class="mt-3 small">
                            <h5>Selected Location</h5>
                            <p>Area: <span id="areaName">N/A</span></p>
                            <p>Latitude: <span id="latitude">N/A</span></p>
                            <p>Longitude: <span id="longitude">N/A</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img src="../img/CLIX.svg" width="46">
                <small class="d-block mb-3 text-body-secondary">©2024</small>
                <p class="small text-body-secondary">
                    Why CLIX?<br>
                    Convenient Living<br>
                    Integrated Experience
                </p>
            </div>
            <div class="col-3">
                <h5>Links</h5>
                <ul class=" list-unstyled">
                    <li><a class="link-secondary text-decoration-none small" href="#">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="#">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Phone: +8801712345678</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/leaflet.js"></script>
    <script src="../js/iot.js"></script>
</body>

</html>