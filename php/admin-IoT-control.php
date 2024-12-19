<?php
session_start();
require_once './db-connection.php';

// Check if user is logged in
if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];
$errorMessage = '';
$successMessage = '';

// Check if user is admin
$clientCheckQuery = "SELECT a._admin_id_ 
                    FROM admin_table a 
                    WHERE a._admin_id_ = ?";

try {
    $stmt = mysqli_prepare($conn, $clientCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        header("Location: access-denied.php");
        exit;
    }

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
        $imageSrc = "../img/user-rounded-svgrepo-com.jpg";
    }

    // Pagination setup
    $page_requests = isset($_GET['page_requests']) ? (int)$_GET['page_requests'] : 1;
    $page_iot = isset($_GET['page_iot']) ? (int)$_GET['page_iot'] : 1;
    $limit = 10;
    $offset_requests = ($page_requests - 1) * $limit;
    $offset_iot = ($page_iot - 1) * $limit;

    // Search parameters
    $search_requests = isset($_GET['search_requests']) ? mysqli_real_escape_string($conn, $_GET['search_requests']) : '';
    $search_iot = isset($_GET['search_iot']) ? mysqli_real_escape_string($conn, $_GET['search_iot']) : '';

    // Build search conditions
    $requestSearchCondition = !empty($search_requests) ? 
        " AND (r._request_id_ LIKE '%$search_requests%' OR 
               r._user_id_ LIKE '%$search_requests%' OR 
               r._iot_id_ LIKE '%$search_requests%')" : "";

    $iotSearchCondition = !empty($search_iot) ? 
        " AND (i._iot_id_ LIKE '%$search_iot%' OR 
               i._iot_label_ LIKE '%$search_iot%')" : "";

    // Count total requests
    $countRequestsQuery = "SELECT COUNT(*) as total 
                          FROM request_table r 
                          WHERE 1=1 $requestSearchCondition";
    $countRequestsResult = mysqli_query($conn, $countRequestsQuery);
    $totalRequests = mysqli_fetch_assoc($countRequestsResult)['total'];
    $totalPagesRequests = ceil($totalRequests / $limit);

    // Count total IoT devices
    $countIoTQuery = "SELECT COUNT(*) as total 
                      FROM iot_table i 
                      WHERE 1=1 $iotSearchCondition";
    $countIoTResult = mysqli_query($conn, $countIoTQuery);
    $totalIoT = mysqli_fetch_assoc($countIoTResult)['total'];
    $totalPagesIoT = ceil($totalIoT / $limit);

    // Get Requests with pagination
    $requestsQuery = "SELECT r.*, 
                            CASE 
                                WHEN ar._active_request_id_ IS NOT NULL THEN 'Active'
                                WHEN pr._pending_request_id_ IS NOT NULL THEN 'Pending'
                                WHEN dr._declined_request_id_ IS NOT NULL THEN 'Declined'
                                ELSE 'Unknown'
                            END as status
                     FROM request_table r
                     LEFT JOIN active_request_table ar ON r._request_id_ = ar._active_request_id_
                     LEFT JOIN pending_request_table pr ON r._request_id_ = pr._pending_request_id_
                     LEFT JOIN declined_request_table dr ON r._request_id_ = dr._declined_request_id_
                     WHERE 1=1 $requestSearchCondition
                     ORDER BY r._request_time_ DESC
                     LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $requestsQuery);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset_requests);
    mysqli_stmt_execute($stmt);
    $requests = mysqli_stmt_get_result($stmt);

    // Get IoT Devices with pagination
    $iotQuery = "SELECT i.*, 
                        CASE 
                            WHEN ai._active_iot_id_ IS NOT NULL THEN 'Active'
                            WHEN ii._inactive_iot_id_ IS NOT NULL THEN 'Inactive'
                            WHEN ui._unpaid_iot_id_ IS NOT NULL THEN 'Unpaid'
                            ELSE 'Unknown'
                        END as status,
                        CASE 
                            WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
                            WHEN w._water_id_ IS NOT NULL THEN 'Water'
                            WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                            ELSE 'Unknown'
                        END as utility_type
                 FROM iot_table i
                 LEFT JOIN active_iot_table ai ON i._iot_id_ = ai._active_iot_id_
                 LEFT JOIN inactive_iot_table ii ON i._iot_id_ = ii._inactive_iot_id_
                 LEFT JOIN unpaid_iot_table ui ON i._iot_id_ = ui._unpaid_iot_id_
                 LEFT JOIN utility_table u ON i._utility_id_ = u._utility_id_
                 LEFT JOIN electricity_table e ON u._utility_id_ = e._electricity_id_
                 LEFT JOIN water_table w ON u._utility_id_ = w._water_id_
                 LEFT JOIN gas_table g ON u._utility_id_ = g._gas_id_
                 WHERE 1=1 $iotSearchCondition
                 ORDER BY i._last_reported_time_ DESC
                 LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $iotQuery);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset_iot);
    mysqli_stmt_execute($stmt);
    $iotDevices = mysqli_stmt_get_result($stmt);

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/admin-outage.css">
</head>

<body>
    <!-- Header -->
    <header class="border-bottom" id="header-section">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../index.php" class="d-flex align-items-center mb-lg-0">
                    <img src="../img/CLIX-white.svg" id="header-logo" alt="Logo" class="img-fluid">
                </a>
                
                <!-- Navbar -->
                <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <ul class="nav">
                        <li><a href="./admin-dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-secondary">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                        <li><a href="./admin-user-control.php" class="nav-link px-3 link-body-emphasis">Client</a></li>
                        <li><a href="./admin-feedback.php" class="nav-link px-3 link-body-emphasis">Feedback</a></li>
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
                        <li><a href="./admin-dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-secondary">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                        <li><a href="./admin-user-control.php" class="nav-link px-3 link-body-emphasis">Client</a></li>
                        <li><a href="./admin-feedback.php" class="nav-link px-3 link-body-emphasis">Feedback</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Section -->
<!-- Main Section -->
<main id="main-section">
    <h2 id="sub-div-header">IoT Control</h2>

    <!-- Requests Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">IoT Requests</h5>
            <div class="mb-3">
                <input type="text" class="form-control" id="search-requests" 
                       placeholder="🔍 Search requests..." 
                       value="<?php echo htmlspecialchars($search_requests); ?>">
            </div>
            
            <div class="table-responsive">
                <table class="table table-borderless small">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>User ID</th>
                            <th>IoT ID</th>
                            <th>Request Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($requests)) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['_request_id_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_user_id_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_iot_id_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_request_time_']); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo match($row['status']) {
                                            'Active' => 'bg-success',
                                            'Pending' => 'bg-warning',
                                            'Declined' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-link p-0" onclick="editRequest(<?php echo $row['_request_id_']; ?>)">
                                        <img src="../img/edit-svgrepo-com.svg" alt="Edit" style="width: 16px; height: 16px;">
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPagesRequests > 0): ?>
                <div class="d-flex justify-content-center" id="pagination-section">
                    <nav aria-label="Page navigation">
                        <ul class="pagination no-border">
                            <?php if ($page_requests > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_requests=<?php echo ($page_requests - 1); ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif;

                            $start_page = max(1, min($page_requests - 2, $totalPagesRequests - 4));
                            $end_page = min($totalPagesRequests, $start_page + 4);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_requests=1&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif;
                            endif;

                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page_requests) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_requests=<?php echo $i; ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                            if ($end_page < $totalPagesRequests): ?>
                                <?php if ($end_page < $totalPagesRequests - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_requests=<?php echo $totalPagesRequests; ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>"><?php echo $totalPagesRequests; ?></a>
                                </li>
                            <?php endif;

                            if ($page_requests < $totalPagesRequests): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_requests=<?php echo ($page_requests + 1); ?>&search_requests=<?php echo urlencode($search_requests); ?>&page_iot=<?php echo $page_iot; ?>&search_iot=<?php echo urlencode($search_iot); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- IoT Devices Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Registered IoT Devices</h5>
            <div class="mb-3">
                <input type="text" class="form-control" id="search-iot" 
                       placeholder="🔍 Search IoT devices..." 
                       value="<?php echo htmlspecialchars($search_iot); ?>">
            </div>
            
            <div class="table-responsive">
                <table class="table table-borderless small">
                    <thead>
                        <tr>
                            <th>IoT ID</th>
                            <th>Label</th>
                            <th>Utility Type</th>
                            <th>Location</th>
                            <th>Last Reported</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($iotDevices)) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['_iot_id_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_iot_label_']); ?></td>
                                <td><?php echo htmlspecialchars($row['utility_type']); ?></td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars($row['_iot_latitude_']) . ', ' . 
                                             htmlspecialchars($row['_iot_longitude_']); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['_last_reported_time_']); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo match($row['status']) {
                                            'Active' => 'bg-success',
                                            'Inactive' => 'bg-warning',
                                            'Unpaid' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-link p-0" onclick="editIoT(<?php echo $row['_iot_id_']; ?>)">
                                        <img src="../img/edit-svgrepo-com.svg" alt="Edit" style="width: 16px; height: 16px;">
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPagesIoT > 0): ?>
                <div class="d-flex justify-content-center" id="pagination-section">
                    <nav aria-label="Page navigation">
                        <ul class="pagination no-border">
                            <?php if ($page_iot > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_iot=<?php echo ($page_iot - 1); ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif;

                            $start_page = max(1, min($page_iot - 2, $totalPagesIoT - 4));
                            $end_page = min($totalPagesIoT, $start_page + 4);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_iot=1&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif;
                            endif;

                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page_iot) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_iot=<?php echo $i; ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                            if ($end_page < $totalPagesIoT): ?>
                                <?php if ($end_page < $totalPagesIoT - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_iot=<?php echo $totalPagesIoT; ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>"><?php echo $totalPagesIoT; ?></a>
                                </li>
                            <?php endif;

                            if ($page_iot < $totalPagesIoT): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_iot=<?php echo ($page_iot + 1); ?>&search_iot=<?php echo urlencode($search_iot); ?>&page_requests=<?php echo $page_requests; ?>&search_requests=<?php echo urlencode($search_requests); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

    <!-- Footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="text-center">
            <p class="mb-0">© 2024 CLIX. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/leaflet.js"></script>
    <script src="../js/admin-outage.js"></script>
    
</body>

</html>