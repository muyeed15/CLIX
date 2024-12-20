<?php
global $conn;

$isLoggedIn = isset($_SESSION['_user_id_']);
$notifications = [];
$imageSrc = "";
$currentPage = basename($_SERVER['PHP_SELF']);

$clientCheckQuery = "SELECT c._client_id_ 
                    FROM client_table c 
                    WHERE c._client_id_ = ?";

if ($isLoggedIn) {
    $user_id = $_SESSION['_user_id_'];

    $stmt = mysqli_prepare($conn, $clientCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        header("Location: access-denied.php");
        exit;
    }

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

        if (($row = mysqli_fetch_assoc($picture)) && (!empty($row['_profile_picture_']))) {
            $pictureData = $row['_profile_picture_'];
            $base64Image = base64_encode($pictureData);
            $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
        } else {
            $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
        }

        mysqli_stmt_close($stmt);

    } catch (Exception $e) {
        echo "Error fetching data: " . $e->getMessage();
    }
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
                <img src="../img/CLIX.svg" id="header-logo" alt="Logo" class="img-fluid">
            </a>

            <!-- Navbar -->
            <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                <ul class="nav">
                    <li><a href="../" class="nav-link px-3 <?php echo isActiveLink('index.php'); ?>">Home</a></li>
                    <?php if (!$isLoggedIn): ?>
                        <li><a href="./about.php" class="nav-link px-3 <?php echo isActiveLink('about.php'); ?>">About
                                Us</a></li>
                        <li><a href="./contact.php" class="nav-link px-3 <?php echo isActiveLink('contact.php'); ?>">Contact
                                Us</a></li>
                    <?php else: ?>
                        <li><a href="./dashboard.php"
                               class="nav-link px-3 <?php echo isActiveLink('dashboard.php'); ?>">Dashboard</a></li>
                        <li><a href="./history.php" class="nav-link px-3 <?php echo isActiveLink('history.php'); ?>">History</a>
                        </li>
                        <li><a href="./outage.php" class="nav-link px-3 <?php echo isActiveLink('outage.php'); ?>">Outage</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Notification, Mobile Navbar and User Section -->
            <div class="d-flex align-items-center">
                <!-- Mobile Navbar Toggle -->
                <button class="navbar-toggler d-lg-none" type="button" style="width: 50px; height: 50px;"
                        data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon d-flex align-items-center justify-content-center"
                              style="width: 100%; height: 100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000"
                                 class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                      d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </span>
                </button>

                <?php if ($isLoggedIn): ?>
                    <!-- Notifications -->
                    <div class="dropdown text-end me-2" id="notification-icon">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17px" fill="currentColor" class="bi bi-bell"
                                 viewBox="0 0 16 16">
                                <path
                                    d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <?php while ($row = mysqli_fetch_assoc($notifications)) : ?>
                                <li><a class="dropdown-item small"
                                       href="#"><?= htmlspecialchars($row['_notification_message_']); ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- User Picture -->
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle"
                           data-bs-toggle="dropdown">
                            <img src="<?php echo $imageSrc; ?>" alt="User" class="rounded-circle"
                                 style="width: 36px; height: 36px;">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="./profile.php">Profile</a></li>
                            <li><a class="dropdown-item small" href="./settings.php">Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item small" href="./logout.php">Sign out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Login and Signup Buttons -->
                    <div class="d-flex gap-2">
                        <a href="./login.php" class="btn btn-outline-primary">Login</a>
                        <a href="./signup.php" class="btn btn-primary">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Collapsible Mobile Menu -->
        <div class="collapse" id="mobileNav">
            <nav class="navbar-nav">
                <ul class="nav flex-column text-center">
                    <li><a href="../" class="nav-link px-3 <?php echo isActiveLink('index.php'); ?>">Home</a></li>
                    <?php if (!$isLoggedIn): ?>
                        <li><a href="./about.php" class="nav-link px-3 <?php echo isActiveLink('about.php'); ?>">About
                                Us</a></li>
                        <li><a href="./contact.php" class="nav-link px-3 <?php echo isActiveLink('contact.php'); ?>">Contact
                                Us</a></li>
                    <?php else: ?>
                        <li><a href="./dashboard.php"
                               class="nav-link px-3 <?php echo isActiveLink('dashboard.php'); ?>">Dashboard</a></li>
                        <li><a href="./history.php" class="nav-link px-3 <?php echo isActiveLink('history.php'); ?>">History</a>
                        </li>
                        <li><a href="./outage.php" class="nav-link px-3 <?php echo isActiveLink('outage.php'); ?>">Outage</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>
