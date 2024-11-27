<?php
session_start();
require_once 'db-connection.php';

if (!isset($_SESSION['nid'])) {
    header("Location: login.php");
    exit;
}

$nid = $_SESSION['nid'];

try {
    // Notification
    $notificationQuery = "SELECT * 
                        FROM (
                            SELECT * 
                            FROM notification_t 
                            WHERE (_nid_ = ? OR _nid_ IS NULL)
                            ORDER BY _date_ DESC, _time_ DESC 
                            LIMIT 10
                        ) AS _notifications_";

    $stmt = mysqli_prepare($conn, $notificationQuery);
    mysqli_stmt_bind_param($stmt, "s", $nid);
    mysqli_stmt_execute($stmt);
    $notifications = mysqli_stmt_get_result($stmt);

    // User Picture
    $pictureQuery = "SELECT _picture_
                    FROM user_t
                    WHERE _nid_ = ?";

    $stmt = mysqli_prepare($conn, $pictureQuery);
    mysqli_stmt_bind_param($stmt, "i", $nid);
    mysqli_stmt_execute($stmt);
    $picture = mysqli_stmt_get_result($stmt);

    if (($row = mysqli_fetch_assoc($picture)) && (!empty($row['_picture_']) && $row['_picture_'] !== NULL)) {
        $pictureData = $row['_picture_'];
        $base64Image = base64_encode($pictureData);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "../img/user-rounded-svgrepo-com.jpg";
    }

    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}
?>

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
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../index.php" class="d-flex align-items-center mb-lg-0">
                    <img src="../img/CLIX.svg" id="header-logo" alt="Logo" class="img-fluid">
                </a>
                
                <!-- Navbar -->
                <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <ul class="nav">
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./pay.php" class="nav-link px-3 link-body-emphasis">Pay Bill</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-secondary">Outage Area</a></li>
                    </ul>
                </nav>

                <!-- Notification, Mobile Navbar and User Section -->
                <div class="d-flex align-items-center">
                    <!-- Mobile Navbar Toggle -->
                    <button class="navbar-toggler d-lg-none" type="button" style="width: 50px; height: 50px;" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </span>
                    </button>

                    <!-- Notifications -->
                    <div class="dropdown text-end me-2" id="notification-icon">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17px" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <?php while ($row = mysqli_fetch_assoc($notifications)) : ?>
                                <li><a class="dropdown-item small" href="#"><?= htmlspecialchars($row['_message_']); ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- User Picture -->
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?php echo $imageSrc; ?>" alt="User" class="rounded-circle" style="width: 36px; height: 36px;">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="./profile.php">Profile</a></li>
                            <li><a class="dropdown-item small" href="./settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="./logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Collapsible Mobile Menu -->
            <div class="collapse" id="mobileNav">
                <nav class="navbar-nav">
                    <ul class="nav flex-column text-center">
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./pay.php" class="nav-link px-3 link-body-emphasis">Pay Bill</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-secondary">Outage Area</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- main -->
    <main id="main-section">
        <h2 id="sub-div-header">Outage Map</h2>
        <div class="mb-3">
            <input type="text" id="areaInput" class="form-control" placeholder="🔍 Search area" autocomplete="off">
            <ul id="suggestions" class="list-group"></ul>
        </div>
        <div id="map"></div>
        <div class="d-flex justify-content-center mt-3">
            <div class="px-3" id="map-gas">■ Gas</div>
            <div class="px-3" id="map-water">■ Water</div>
            <div class="px-3" id="map-electricity">■ Electricity</div>
        </div>
        <div class="mt-3 small d-none">
            <h5>Selected Location</h5>
            <p>Area: <span id="areaName">N/A</span></p>
            <p>Latitude: <span id="latitude">N/A</span></p>
            <p>Longitude: <span id="longitude">N/A</span></p>
        </div>

        <div id="table-section">
            <div>
                <h2 id="sub-div-header">Outage List</h2>
                <div class="py-1">
                    <input class="form-control" id="client-search" type="search" placeholder="🔍 Search area" aria-label="Search" style="width: 300px;">
                </div>
            </div>
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
                <img class="footer-logo" src="../img/CLIX.svg">
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
                    <li><a class="link-secondary text-decoration-none small" href="./about.php">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./contact.php">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./privacy.php">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./term.php">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./faq.php">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="mailto:clix@mail.com">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="">Phone: +8801712345678</a></li>
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