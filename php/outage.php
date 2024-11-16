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
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/outage.css">
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
                    <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                    <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                    <li><a href="./pay.php" class="nav-link px-3 link-body-emphasis">Pay Bill</a></li>
                    <li><a href="./outage.php" class="nav-link px-3 link-secondary">Outage Area</a></li>
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
        <div id="map-section">
            <h2 id="sub-div-header">Outage Map</h2>
            <div id="map"></div>
            <div class="d-flex justify-content-center">
                <div class="px-3" id="map-gas">■ Gas</div>
                <div class="px-3" id="map-water">■ Water</div>
                <div class="px-3" id="map-electricity">■ Electricity</div>
            </div>
        </div>

        <div id="table-section">
            <h2 id="sub-div-header">Outage List</h2>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th scope="col" width=1vw>Type</th>
                        <th scope="col">Area</th>
                        <th scope="col">Outage</th>
                        <th scope="col">Start</th>
                        <th scope="col">End</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/gas-costs-svgrepo-com.svg"></td>
                        <td>Dhanmondi, Dhaka</td>
                        <td>Gas Outage</td>
                        <td>10:00am (12-Nov-2024)</td>
                        <td>07:00pm (12-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/hydropower-coal-svgrepo-com.svg"></td>
                        <td>Banani, Dhaka</td>
                        <td>Electricity Outage</td>
                        <td>11:00am (11-Nov-2024)</td>
                        <td>07:00am (12-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/water-fee-svgrepo-com.svg"></td>
                        <td>Gulshan, Dhaka</td>
                        <td>Water Outage</td>
                        <td>03:00pm (16-Nov-2024)</td>
                        <td>05:00pm (17-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/gas-costs-svgrepo-com.svg"></td>
                        <td>Mohammadpur, Dhaka</td>
                        <td>Gas Outage</td>
                        <td>02:00pm (12-Nov-2024)</td>
                        <td>08:00pm (12-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/hydropower-coal-svgrepo-com.svg"></td>
                        <td>Mirpur, Dhaka</td>
                        <td>Electricity Outage</td>
                        <td>09:00am (10-Nov-2024)</td>
                        <td>03:00pm (10-Nov-2024)</td>
                    </tr>
                    <tr>
                       <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/water-fee-svgrepo-com.svg"></td>
                        <td>Uttara, Dhaka</td>
                        <td>Water Outage</td>
                        <td>01:00pm (13-Nov-2024)</td>
                        <td>09:00am (14-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/gas-costs-svgrepo-com.svg"></td>
                        <td>Bashundhara, Dhaka</td>
                        <td>Gas Outage</td>
                        <td>10:00am (11-Nov-2024)</td>
                        <td>04:00pm (11-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/hydropower-coal-svgrepo-com.svg"></td>
                        <td>Tejgaon, Dhaka</td>
                        <td>Electricity Outage</td>
                        <td>05:00pm (15-Nov-2024)</td>
                        <td>10:00pm (15-Nov-2024)</td>
                    </tr>
                    <tr>
                        <td class="d-flex justify-content-center"><img class="utility-svg" src="../img/water-fee-svgrepo-com.svg"></td>
                        <td>Rampura, Dhaka</td>
                        <td>Water Outage</td>
                        <td>09:00am (14-Nov-2024)</td>
                        <td>06:00pm (14-Nov-2024)</td>
                    </tr>
                </tbody>
            </table>
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
    <script src="../js/leaflet.js"></script>
    <script src="../js/outage.js"></script>
</body>

</html>