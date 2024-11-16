<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<!-- body -->

<body>
    <!-- header -->
    <header class="border-bottom" id="header-section">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <a href="#">
                    <img class="py-2" src="../img/CLIX.svg" id="header-logo">
                </a>

                <ul class="nav small py-2">
                    <li><a href="./admin-dashboard.php" class="nav-link px-3 link-secondary">Dashboard</a></li>
                    <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                    <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">Payment</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
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

    <!-- main -->
    <main id="main-section">
        <h2 id="sub-div-header">Dashboard</h2>

        <div style="display: flex; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 0 0 66.666%; padding: 0.5%;">
                <div class="card">
                    <div class="card-body">
                        <canvas id="chLine"></canvas>
                    </div>
                </div>
            </div>
            <div style="flex: 0 0 33.333%; padding: 0.5%;">
                <div class="card">
                    <div class="card-body">
                        <canvas id="chDonut1"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h2 id="sub-div-header">Your Devices</h2>
            <table class="table table-borderless" id="iot-table">
                <thead id="iot-thead">
                    <tr>
                        <th scope="col" width=1vw>Device</th>
                        <th scope="col">ID</th>
                        <th scope="col">Label</th>
                        <th scope="col">Type</th>
                        <th scope="col">Outage</th>
                        <th scope="col">Usage</th>
                        <th scope="col">Total Usage</th>
                        <th scope="col">Balance</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/hydropower-coal-svgrepo-com.svg"></td>
                        <td>123456789015</td>
                        <td>Electricity Meter - Basement</td>
                        <td>Electricity</td>
                        <td>Yes</td>
                        <td>↑0.20kW/sec</td>
                        <td>820.60kW</td>
                        <td>8,000tk</td>
                        <td>Active</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/hydropower-coal-svgrepo-com.svg"></td>
                        <td>123456789016</td>
                        <td>Electricity Meter - 2nd Floor</td>
                        <td>Electricity</td>
                        <td>No</td>
                        <td>↑86.00kW/sec</td>
                        <td>132.65kW</td>
                        <td>8,000tk</td>
                        <td style="color:white; background-color:rgb(100, 100, 100);">Inactive</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/gas-costs-svgrepo-com.svg"></td>
                        <td>223456789017</td>
                        <td>Gas Meter - 4th Floor</td>
                        <td>Gas</td>
                        <td>Yes</td>
                        <td>--</td>
                        <td>782.10m<sup>3</sup></td>
                        <td>8,000tk</td>
                        <td>Active</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/gas-costs-svgrepo-com.svg"></td>
                        <td>223456789018</td>
                        <td>Gas Meter - Basement</td>
                        <td>Gas</td>
                        <td>No</td>
                        <td>↑2.00m<sup>3</sup>/sec</td>
                        <td>250.68m<sup>3</sup></td>
                        <td>8,000tk</td>
                        <td style="color:white; background-color:rgb(100, 100, 100);">Inactive</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/water-fee-svgrepo-com.svg"></td>
                        <td>323456789019</td>
                        <td>Water Meter - 1st Floor</td>
                        <td>Water</td>
                        <td>No</td>
                        <td>↑0.30L/sec</td>
                        <td>1200.32L</td>
                        <td>8,000tk</td>
                        <td>Active</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- footer -->
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

    <!-- script -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/chart.js"></script>
    <script src="../js/chart.script.js"></script>
</body>

</html>