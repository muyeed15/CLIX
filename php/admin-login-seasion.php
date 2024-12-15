<?php
session_start();
require_once './db-connection.php';

if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];

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
        $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
    }

    // User Login Sessions
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    $totalQuery = "SELECT COUNT(*) as total 
                  FROM user_login_log_table l
                  JOIN user_table u ON l._user_id_ = u._user_id_";
    
    if (!empty($search)) {
        if (is_numeric($search)) {
            $totalQuery .= " WHERE u._user_id_ = ?";
            $stmt = mysqli_prepare($conn, $totalQuery);
            mysqli_stmt_bind_param($stmt, "i", $search);
        } else {
            $totalQuery .= " WHERE u._first_name_ LIKE ? OR u._last_name_ LIKE ? OR u._email_ LIKE ? OR l._device_name_ LIKE ?";
            $stmt = mysqli_prepare($conn, $totalQuery);
            $searchParam = "%$search%";
            mysqli_stmt_bind_param($stmt, "ssss", $searchParam, $searchParam, $searchParam, $searchParam);
        }
    } else {
        $stmt = mysqli_prepare($conn, $totalQuery);
    }
    
    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalSessions = $totalRow['total'];
    $totalPages = ceil($totalSessions / $itemsPerPage);

    $query = "SELECT l.*, u._user_id_, u._first_name_, u._last_name_, u._email_
              FROM user_login_log_table l
              JOIN user_table u ON l._user_id_ = u._user_id_";
    
    if (!empty($search)) {
        if (is_numeric($search)) {
            $query .= " WHERE u._user_id_ = ?";
            $query .= " ORDER BY l._log_time_ DESC LIMIT ? OFFSET ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iii", $search, $itemsPerPage, $offset);
        } else {
            $query .= " WHERE u._first_name_ LIKE ? OR u._last_name_ LIKE ? OR u._email_ LIKE ? OR l._device_name_ LIKE ?";
            $query .= " ORDER BY l._log_time_ DESC LIMIT ? OFFSET ?";
            $stmt = mysqli_prepare($conn, $query);
            $searchParam = "%$search%";
            mysqli_stmt_bind_param($stmt, "ssssii", $searchParam, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        }
    } else {
        $query .= " ORDER BY l._log_time_ DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $itemsPerPage, $offset);
    }
    
    mysqli_stmt_execute($stmt);
    $loginSessions = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
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
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-secondary">Seasion</a></li>
                        <li><a href="./admin-user-control" class="nav-link px-3 link-body-emphasis">Client</a></li>
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
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-secondary">Seasion</a></li>
                        <li><a href="./admin-user-control" class="nav-link px-3 link-body-emphasis">Client</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Section -->
    <main id="main-section">
        <h2 id="sub-div-header">User Login Sessions</h2>

        <div class="card mb-4" id="user-login-card">
            <div class="card-body">
                <div class="mb-3">
                    <form id="searchForm" method="GET" action="">
                        <input type="text" class="form-control" id="search-users" name="search" 
                            placeholder="🔍 Search by User ID, Name, Email, or Device" 
                            value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </form>
                </div>
                <table class="table table-borderless small" id="user-login-table">
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Location (Lat, Long)</th>
                            <th>IP Address</th>
                            <th>Device Type</th>
                            <th>Date and Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($loginSessions)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['_user_log_id_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_user_id_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_first_name_'] . ' ' . $row['_last_name_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_email_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_device_latitude_'] . ', ' . $row['_device_longitude_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_ip_address_']); ?></td>
                                <td><?php echo htmlspecialchars($row['_device_name_']); ?></td>
                                <td><?php echo date('h:ia (d-M-Y)', strtotime($row['_log_time_'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 0): ?>
                <div class="d-flex justify-content-center" id="pagination-section" <?php echo ($totalPages <= 1) ? 'style="display: none !important;"' : ''; ?>>
                    <nav aria-label="Page navigation">
                        <ul class="pagination no-border">
                            <?php
                            if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif;

                            $start_page = max(1, min($page - 2, $totalPages - 4));
                            $end_page = min($totalPages, $start_page + 4);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif;
                            endif;

                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                            if ($end_page < $totalPages): ?>
                                <?php if ($end_page < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>"><?php echo $totalPages; ?></a>
                                </li>
                            <?php endif;

                            if ($page < $totalPages): ?>
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