<?php
session_start();
require_once './db-connection.php';

if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];

try {
    // Notification
    $notificationQuery = "SELECT * FROM notification_table
                        WHERE _user_id_ = ? OR _user_id_ IS NULL
                        ORDER BY _notification_time_ DESC
                        LIMIT 10";

    $stmt = mysqli_prepare($conn, $notificationQuery);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $notifications = mysqli_stmt_get_result($stmt);

    // User Picture
    $pictureQuery = "SELECT _profile_picture_ FROM user_table
                    WHERE _user_id_ = ?";

    $stmt = mysqli_prepare($conn, $pictureQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $picture = mysqli_stmt_get_result($stmt);

    if (($row = mysqli_fetch_assoc($picture)) && (!empty($row['_profile_picture_']) && $row['_profile_picture_'] !== NULL)) {
        $pictureData = $row['_profile_picture_'];
        $base64Image = base64_encode($pictureData);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
    }

    // Outage
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    $totalQuery = "SELECT COUNT(*) as total FROM outage_table o";
    if (!empty($search)) {
        $totalQuery .= " WHERE o._affected_area_ LIKE ?";
    }

    $stmt = mysqli_prepare($conn, $totalQuery);
    if (!empty($search)) {
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "s", $searchParam);
    }
    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalOutages = $totalRow['total'];
    $totalPages = ceil($totalOutages / $itemsPerPage);

    $query = "SELECT o.*, u._utility_id_ 
            FROM outage_table o 
            JOIN utility_table u ON o._utility_id_ = u._utility_id_";
    if (!empty($search)) {
        $query .= " WHERE o._affected_area_ LIKE ?";
    }
    $query .= " ORDER BY o._start_time_ DESC LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $query);
    if (!empty($search)) {
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "sii", $searchParam, $itemsPerPage, $offset);
    } else {
        mysqli_stmt_bind_param($stmt, "ii", $itemsPerPage, $offset);
    }
    mysqli_stmt_execute($stmt);
    $outages = mysqli_stmt_get_result($stmt);

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
    <link rel="stylesheet" href="../css/animation.css">
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
                        <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-secondary">Outage</a></li>
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
                                <li><a class="dropdown-item small" href="#"><?= htmlspecialchars($row['_notification_message_']); ?></a></li>
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
                        <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-secondary">Outage</a></li>
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
                    <form id="searchForm" method="GET" action="">
                        <input class="form-control" id="client-search" name="search" type="search" 
                            placeholder="🔍 Search area" aria-label="Search" style="width: 300px;"
                            value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </form>
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
                <tbody id="outageTableBody">
                    <?php while ($row = mysqli_fetch_assoc($outages)): ?>
                        <tr>
                            <td class="d-flex justify-content-center">
                                <?php
                                $utilityIcon = '';
                                $outageType = '';
                                if (isset($row['_utility_id_'])) {
                                    switch($row['_utility_id_']) {
                                        case 1:
                                            $utilityIcon = '../img/gas-costs-svgrepo-com.svg';
                                            $outageType = 'Gas Outage';
                                            break;
                                        case 2:
                                            $utilityIcon = '../img/water-fee-svgrepo-com.svg';
                                            $outageType = 'Water Outage';
                                            break;
                                        case 3:
                                            $utilityIcon = '../img/hydropower-coal-svgrepo-com.svg';
                                            $outageType = 'Electricity Outage';
                                            break;
                                    }
                                }
                                ?>
                                <img class="utility-svg" src="<?php echo $utilityIcon; ?>">
                            </td>
                            <td><?php echo htmlspecialchars($row['_affected_area_']); ?></td>
                            <td><?php echo $outageType; ?></td>
                            <td><?php echo date('h:ia (d-M-Y)', strtotime($row['_start_time_'])); ?></td>
                            <td><?php echo date('h:ia (d-M-Y)', strtotime($row['_end_time_'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if ($totalPages > 0): ?>
                <div class="d-flex justify-content-center" id="pagination-section" <?php echo ($totalPages <= 1) ? 'style="display: none !important;"' : ''; ?>>
                    <nav aria-label="Page navigation">
                        <ul class="pagination no-border">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
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
