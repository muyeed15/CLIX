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
                    <li><a href="./admin-user-control.php" class="nav-link px-3 link-secondary">Client</a></li>
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
        <h2 id="sub-div-header">User List</h2>

        <div class="mb-3">
            <label for="search-users" class="form-label">Search User</label>
            <input type="text" class="form-control" id="search-users" placeholder="🔍 Search by Name, NID, or Address">
        </div>
        
        <div class="card mb-4" id="user-login-card">
            <div class="card-body">
            <table class="table table-borderless small" id="user-login-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Name</th>
                        <th>NID</th>
                        <th>Address</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody id="user-login-table-body">
                    <tr>
                        <td><img src="../img/LinkedIn_1x1_1000px.jpg" alt="User Picture" width="32" height="32" class="rounded-circle"></td>
                        <td>Syed Abdullah Al Muyeed</td>
                        <td>1234567890123</td>
                        <td>Miprur, Dhaka</td>
                        <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                    </tr>
                    <tr>
                        <td><img src="../img/dipra_1000px.jpeg" alt="User Picture" width="32" height="32" class="rounded-circle"></td>
                        <td>Nafisa Anzum Dipra</td>
                        <td>1134567890123</td>
                        <td>Karwan Bazar, Dhaka</td>
                        <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                    </tr>
                    <tr>
                        <td><img src="../img/alam_1000px.jpeg" alt="User Picture" width="32" height="32" class="rounded-circle"></td>
                        <td>Ishrak Alam</td>
                        <td>2534567890123</td>
                        <td>Karwan Bazar, Dhaka</td>
                        <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                    </tr>
                    <tr>
                        <td><img src="../img/shrabon_1000px.jpeg" alt="User Picture" width="32" height="32" class="rounded-circle"></td>
                        <td>Shrabon Das</td>
                        <td>4234567890123</td>
                        <td>Rampura, Dhaka</td>
                        <td class="d-flex"><a href="./admin-IoT-edit.php"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></a></td>
                    </tr>
                    <tr>
                        <td><img src="../img/imitiaz_1000px.png" alt="User Picture" width="32" height="32" class="rounded-circle"></td>
                        <td>A.H.M. Imtiaz</td>
                        <td>3234567890123</td>
                        <td>Bashundhara R/A, Dhaka</td>
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