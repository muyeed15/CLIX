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
                    <li><a href="./admin-login-season.php" class="nav-link px-3 link-secondary">Seasion</a></li>
                    <li><a href="./admin-user-control.php" class="nav-link px-3 link-body-emphasis">Client</a></li>
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
        <h2 id="sub-div-header">User Login Sessions</h2>

        <div class="mb-3">
            <label for="search-users" class="form-label">Search User Login Sessions</label>
            <input type="text" class="form-control" id="search-users" placeholder="🔍 Search by Name, NID, or Area">
        </div>
        
        <div class="card mb-4" id="user-login-card">
            <div class="card-body">
                <table class="table table-borderless small" id="user-login-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>NID</th>
                            <th>Area</th>
                            <th>Location (Lat, Long)</th>
                            <th>Public IP Address</th>
                            <th>Device Type</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="user-login-table-body">
                        <tr>
                            <td>1</td>
                            <td>Hasan Ahmed</td>
                            <td>1981234567890</td>
                            <td>Dhaka</td>
                            <td>23.8103, 90.4125</td>
                            <td>103.78.100.1</td>
                            <td>Smartphone</td>
                            <td>2024-11-16</td>
                            <td>14:30</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Ayesha Begum</td>
                            <td>1982345678901</td>
                            <td>Chattogram</td>
                            <td>22.3569, 91.7832</td>
                            <td>103.79.150.2</td>
                            <td>Computer</td>
                            <td>2024-11-16</td>
                            <td>10:00</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Kamal Hossain</td>
                            <td>1983456789012</td>
                            <td>Sylhet</td>
                            <td>24.9045, 91.8611</td>
                            <td>103.78.200.3</td>
                            <td>Smartphone</td>
                            <td>2024-11-16</td>
                            <td>12:45</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Rahima Khatun</td>
                            <td>1984567890123</td>
                            <td>Khulna</td>
                            <td>22.8456, 89.5403</td>
                            <td>103.79.220.4</td>
                            <td>Smartphone</td>
                            <td>2024-11-16</td>
                            <td>09:20</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Sajjad Khan</td>
                            <td>1985678901234</td>
                            <td>Rajshahi</td>
                            <td>24.3636, 88.6241</td>
                            <td>103.80.100.5</td>
                            <td>Computer</td>
                            <td>2024-11-16</td>
                            <td>18:15</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Mariam Akter</td>
                            <td>1986789012345</td>
                            <td>Barishal</td>
                            <td>22.7010, 90.3535</td>
                            <td>103.81.120.6</td>
                            <td>Smartphone</td>
                            <td>2024-11-16</td>
                            <td>07:45</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Rafiq Islam</td>
                            <td>1987890123456</td>
                            <td>Rangpur</td>
                            <td>25.7439, 89.2752</td>
                            <td>103.82.150.7</td>
                            <td>Computer</td>
                            <td>2024-11-16</td>
                            <td>21:30</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Fahim Rahman</td>
                            <td>1988901234567</td>
                            <td>Mymensingh</td>
                            <td>24.7471, 90.4203</td>
                            <td>103.83.200.8</td>
                            <td>Smartphone</td>
                            <td>2024-11-16</td>
                            <td>08:15</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Sadia Sultana</td>
                            <td>1989012345678</td>
                            <td>Gazipur</td>
                            <td>23.9999, 90.4200</td>
                            <td>103.84.100.9</td>
                            <td>Computer</td>
                            <td>2024-11-16</td>
                            <td>11:00</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Mahbub Alam</td>
                            <td>1990123456789</td>
                            <td>Narayanganj</td>
                            <td>23.6238, 90.5000</td>
                            <td>103.85.150.10</td>
                            <td>Smartphone</td>
                            <td>2024-11-16</td>
                            <td>17:45</td>
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