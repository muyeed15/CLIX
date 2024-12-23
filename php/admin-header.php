<?php
global $conn;
if (!isset($_SESSION['_user_id_'])) {
    header("Location: access-denied.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Check if user is admin
$adminCheckQuery = "SELECT a._admin_id_ 
                    FROM admin_table a 
                    WHERE a._admin_id_ = ?";

try {
    $stmt = mysqli_prepare($conn, $adminCheckQuery);
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

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}

// Function to check if a nav link should be active
function isActiveLink($pageName): string
{
    global $currentPage;
    return $currentPage === $pageName ? 'link-secondary' : 'link-body-emphasis';
}
?>

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
                    <li><a href="./admin-dashboard.php" class="nav-link px-3 <?php echo isActiveLink('admin-dashboard.php'); ?>">Dashboard</a></li>
                    <li><a href="./admin-outage.php" class="nav-link px-3 <?php echo isActiveLink('admin-outage.php'); ?>">Outage</a></li>
                    <li><a href="./admin-IoT-control.php" class="nav-link px-3 <?php echo isActiveLink('admin-IoT-control.php'); ?>">IoT</a></li>
                    <li><a href="./admin-notification.php" class="nav-link px-3 <?php echo isActiveLink('admin-notification.php'); ?>">Notification</a></li>
                    <li><a href="./admin-login-seasion.php" class="nav-link px-3 <?php echo isActiveLink('admin-login-seasion.php'); ?>">Seasion</a></li>
                    <li><a href="./admin-user-control.php" class="nav-link px-3 <?php echo isActiveLink('admin-user-control.php'); ?>">Client</a></li>
                    <li><a href="./admin-feedback.php" class="nav-link px-3 <?php echo isActiveLink('admin-feedback.php'); ?>">Feedback</a></li>
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
                        <li><a class="dropdown-item small" href="./admin-profile.php">Profile</a></li>
                        <li><a class="dropdown-item small" href="./admin-settings.php">Settings</a></li>
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
                    <li><a href="./admin-dashboard.php" class="nav-link px-3 <?php echo isActiveLink('admin-dashboard.php'); ?>">Dashboard</a></li>
                    <li><a href="./admin-outage.php" class="nav-link px-3 <?php echo isActiveLink('admin-outage.php'); ?>">Outage</a></li>
                    <li><a href="./admin-IoT-control.php" class="nav-link px-3 <?php echo isActiveLink('admin-IoT-control.php'); ?>">IoT</a></li>
                    <li><a href="./admin-notification.php" class="nav-link px-3 <?php echo isActiveLink('admin-notification.php'); ?>">Notification</a></li>
                    <li><a href="./admin-login-seasion.php" class="nav-link px-3 <?php echo isActiveLink('admin-login-seasion.php'); ?>">Seasion</a></li>
                    <li><a href="./admin-user-control.php" class="nav-link px-3 <?php echo isActiveLink('admin-user-control.php'); ?>">Client</a></li>
                    <li><a href="./admin-feedback.php" class="nav-link px-3 <?php echo isActiveLink('admin-feedback.php'); ?>">Feedback</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>
