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
                    <li><a href="./admin-iot.php" class="nav-link px-3 link-secondary">IoT</a></li>
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
        <h2 id="sub-div-header">IoT Control</h2>
        <div class="card mb-4" id="create-iot-card">
            <div class="card-body">
                <h5 class="card-title">Register IoT</h5>
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
                        <label for="iot-header" class="form-label">IoT ID</label>
                        <input type="text" class="form-control" id="iot-header" placeholder="Enter IoT ID">
                    </div>
                    <div class="mb-3">
                        <label for="iot-header" class="form-label">NID</label>
                        <input type="text" class="form-control" id="iot-header" placeholder="Enter User NID">
                    </div>
                    <div class="mb-3">
                        <label for="iot-header" class="form-label">Label</label>
                        <input type="text" class="form-control" id="iot-header" placeholder="Enter label">
                    </div>
                    <div class="mb-3">
                        <label for="iot-header" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="iot-header" placeholder="Enter latitude">
                    </div>
                    <div class="mb-3">
                        <label for="iot-header" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="iot-header" placeholder="Enter longitude">
                    </div>
                    <div class="mb-3">
                        <label for="iot-header" class="form-label">Cost per unit</label>
                        <input type="text" class="form-control" id="iot-header" placeholder="Enter cost per unit">
                    </div>
                    <div class="mb-3">
                        <label for="iot-header" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment-status-type">
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Banned">Banned</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>

        <div class="mb-3">
            <label for="search-notifications" class="form-label">Search IoT</label>
            <input type="text" class="form-control" id="search-notifications" placeholder="🔍 Search IoT">
        </div>
        
        <div class="card mb-4" id="create-iot-card">
            <div class="card-body">
                <table class="table table-borderless small" id="iot-table">
                    <thead>
                        <tr>
                            <th>IoT ID</th>
                            <th>Type</th>
                            <th>Label</th>
                            <th>Area</th>
                            <th>Location</th>
                            <th>Balance</th>
                            <th>Outage</th>
                            <th>Usage</th>
                            <th>Total Usage</th>
                            <th>Payment Status</th>
                            <th>Active Status</th>
                            <th>Uptime</th>
                            <th>Total Uptime</th>
                            <th>Date & Time</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody id="iot-table-body">
                        <tr>
                            <td>200000000009</td>
                            <td>Gas</td>
                            <td>Neha Home</td>
                            <td>Dhanmondi</td>
                            <td>(23.7451, 90.3750)</td>
                            <td>700 BDT</td>
                            <td>Leak detected near the main pipe</td>
                            <td>0 m3</td>
                            <td>50 m3</td>
                            <td>Unpaid</td>
                            <td>Inactive</td>
                            <td>1 hour</td>
                            <td>60 hours</td>
                            <td>2024-11-16 15:30</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>100000000004</td>
                            <td>Electricity</td>
                            <td>Ahmed Villa</td>
                            <td>Mirpur</td>
                            <td>(23.8041, 90.3665)</td>
                            <td>350 BDT</td>
                            <td>Power loss from 3:00 PM to 5:00 PM</td>
                            <td>30 kWh</td>
                            <td>200 kWh</td>
                            <td>Paid</td>
                            <td>Active</td>
                            <td>2 hours</td>
                            <td>80 hours</td>
                            <td>2024-11-16 15:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>300000000001</td>
                            <td>Water</td>
                            <td>The Green Nest</td>
                            <td>Banani</td>
                            <td>(23.7890, 90.4000)</td>
                            <td>500 BDT</td>
                            <td>Leak detected on pipeline</td>
                            <td>0 L</td>
                            <td>30 L</td>
                            <td>Paid</td>
                            <td>Inactive</td>
                            <td>3 hours</td>
                            <td>150 hours</td>
                            <td>2024-11-16 14:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>200000000008</td>
                            <td>Gas</td>
                            <td>Sunny Apartments</td>
                            <td>Tejgaon</td>
                            <td>(23.7600, 90.3910)</td>
                            <td>1200 BDT</td>
                            <td>Supply interruption from 10:00 AM</td>
                            <td>50 m3</td>
                            <td>150 m3</td>
                            <td>Paid</td>
                            <td>Active</td>
                            <td>6 hours</td>
                            <td>200 hours</td>
                            <td>2024-11-16 11:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>300000000002</td>
                            <td>Water</td>
                            <td>Ocean View</td>
                            <td>Rampura</td>
                            <td>(23.7651, 90.4200)</td>
                            <td>250 BDT</td>
                            <td>Clogged water drain reported</td>
                            <td>0 L</td>
                            <td>20 L</td>
                            <td>Unpaid</td>
                            <td>Inactive</td>
                            <td>5 hours</td>
                            <td>60 hours</td>
                            <td>2024-11-16 10:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>100000000003</td>
                            <td>Electricity</td>
                            <td>Blue Horizon</td>
                            <td>Uttara</td>
                            <td>(23.8716, 90.4003)</td>
                            <td>100 BDT</td>
                            <td>Fluctuations observed from 5:00 PM</td>
                            <td>10 kWh</td>
                            <td>120 kWh</td>
                            <td>Unpaid</td>
                            <td>Inactive</td>
                            <td>1 hour</td>
                            <td>200 hours</td>
                            <td>2024-11-16 17:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>100000000002</td>
                            <td>Electricity</td>
                            <td>River Edge</td>
                            <td>Banani</td>
                            <td>(23.7890, 90.4000)</td>
                            <td>200 BDT</td>
                            <td>Scheduled maintenance</td>
                            <td>0 kWh</td>
                            <td>0 kWh</td>
                            <td>Paid</td>
                            <td>Active</td>
                            <td>4 hours</td>
                            <td>120 hours</td>
                            <td>2024-11-16 16:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>300000000003</td>
                            <td>Water</td>
                            <td>Hilltop Haven</td>
                            <td>Tejgaon</td>
                            <td>(23.7600, 90.3910)</td>
                            <td>800 BDT</td>
                            <td>Water supply disruption from 9:00 AM</td>
                            <td>50 L</td>
                            <td>100 L</td>
                            <td>Paid</td>
                            <td>Active</td>
                            <td>1 hour</td>
                            <td>80 hours</td>
                            <td>2024-11-16 09:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>200000000006</td>
                            <td>Gas</td>
                            <td>Golden Heights</td>
                            <td>Mirpur</td>
                            <td>(23.8041, 90.3665)</td>
                            <td>1500 BDT</td>
                            <td>Gas leak detected</td>
                            <td>0 m3</td>
                            <td>100 m3</td>
                            <td>Unpaid</td>
                            <td>Inactive</td>
                            <td>2 hours</td>
                            <td>50 hours</td>
                            <td>2024-11-16 13:00</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                        <tr>
                            <td>300000000004</td>
                            <td>Water</td>
                            <td>Crystal Palace</td>
                            <td>Mohammadpur</td>
                            <td>(23.7603, 90.3585)</td>
                            <td>300 BDT</td>
                            <td>Low water pressure</td>
                            <td>0 L</td>
                            <td>50 L</td>
                            <td>Unpaid</td>
                            <td>Inactive</td>
                            <td>2 hours</td>
                            <td>20 hours</td>
                            <td>2024-11-16 12:30</td>
                            <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center" id="pagination-section">
                <nav aria-label="Page navigation example">
                    <ul class="pagination no-border">
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
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
    <script src="../js/admin-outage.js"></script>
</body>

</html>