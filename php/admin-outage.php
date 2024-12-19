<?php
session_start();
require_once './db-connection.php';

// Basic session check
if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];

try {
    // Check admin access
    $stmt = mysqli_prepare($conn, "SELECT a._admin_id_ FROM admin_table a WHERE a._admin_id_ = ?");
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

    // Handle outage form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addOutage'])) {
        mysqli_begin_transaction($conn);
        
        try {
            // Get utility ID based on type
            $utility_type = $_POST['notificationType'];
            $utility_table = strtolower($utility_type) . '_table';
            $utility_id_query = "SELECT ut._utility_id_ 
                                FROM utility_table ut 
                                JOIN $utility_table t ON ut._utility_id_ = t._" . strtolower($utility_type) . "_id_
                                LIMIT 1";
            $utility_result = mysqli_query($conn, $utility_id_query);
            
            if (!$utility_result || mysqli_num_rows($utility_result) === 0) {
                throw new Exception("Invalid utility type");
            }
            
            $utility_id = mysqli_fetch_assoc($utility_result)['_utility_id_'];
            $start_datetime = date('Y-m-d H:i:s', strtotime($_POST['startDate'] . ' ' . $_POST['startTime']));
            $end_datetime = date('Y-m-d H:i:s', strtotime($_POST['endDate'] . ' ' . $_POST['endTime']));
            $radius_km = $_POST['radiusInput'] / 1000;
            $area_name = $_POST['areaName'];
            $latitude = $_POST['latitude'];
            $longitude = $_POST['longitude'];

            // Insert into outage_table
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO outage_table 
                (_utility_id_, _start_time_, _end_time_, _affected_area_, _latitude_, _longitude_, _range_km_)
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            mysqli_stmt_bind_param($stmt, "isssddd",
                $utility_id,
                $start_datetime,
                $end_datetime,
                $area_name,
                $latitude,
                $longitude,
                $radius_km
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to insert outage record");
            }

            $outage_id = mysqli_insert_id($conn);

            // Insert into active_outage_table
            $stmt = mysqli_prepare($conn, "INSERT INTO active_outage_table (_active_outage_id_) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to set outage as active");
            }

            // Find affected IoT devices within range
            $affected_iot_query = "
                SELECT i._iot_id_
                FROM iot_table i
                WHERE i._utility_id_ = ?
                AND ST_Distance_Sphere(
                    point(i._iot_longitude_, i._iot_latitude_),
                    point(?, ?)
                ) <= ? * 1000";

            $stmt = mysqli_prepare($conn, $affected_iot_query);
            mysqli_stmt_bind_param($stmt, "iddd", 
                $utility_id,
                $longitude,
                $latitude,
                $radius_km
            );

            mysqli_stmt_execute($stmt);
            $affected_iots = mysqli_stmt_get_result($stmt);

            while ($iot = mysqli_fetch_assoc($affected_iots)) {
                $stmt = mysqli_prepare($conn, 
                    "INSERT INTO outage_mapping_table (_outage_id_, _iot_id_)
                    VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ii", $outage_id, $iot['_iot_id_']);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to create outage mapping");
                }

                $mapping_id = mysqli_insert_id($conn);
                $impact_type = strtolower($_POST['impactType']);
                $impact_table = $impact_type . '_impact_table';
                $impact_column = '_' . $impact_type . '_impact_id_';

                $stmt = mysqli_prepare($conn, 
                    "INSERT INTO " . $impact_table . " (" . $impact_column . ") 
                    VALUES (?)");

                if (!$stmt) {
                    throw new Exception("Failed to prepare impact statement");
                }

                if (!mysqli_stmt_bind_param($stmt, "i", $mapping_id)) {
                    throw new Exception("Failed to bind impact parameters");
                }

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to insert impact level: " . mysqli_error($conn));
                }
            }

            // Create notification
            $notification_message = "New {$_POST['impactType']} impact {$utility_type} outage reported in {$area_name}";
            $notification_title = "Utility Outage Alert";
            
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO notification_table 
                (_notification_time_, _notification_title_, _notification_message_)
                VALUES (NOW(), ?, ?)");
            
            mysqli_stmt_bind_param($stmt, "ss", $notification_title, $notification_message);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create notification");
            }

            $notification_id = mysqli_insert_id($conn);

            // Insert into alert_notification_table
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO alert_notifiaction_table (_alt_not_id_)
                VALUES (?)");
            mysqli_stmt_bind_param($stmt, "i", $notification_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create alert notification");
            }

            // Insert into outage_alert_notification_table
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO outage_alert_notification_table 
                (_other_alt_not_id_, _outage_id_)
                VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ii", $notification_id, $outage_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create outage alert notification");
            }

            mysqli_commit($conn);
            $_SESSION['success_message'] = "Outage successfully recorded";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Failed to record outage: " . $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Handle status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateStatus'])) {
        $outage_id = intval($_POST['outage_id']);
        $new_status = $_POST['status'];
        
        mysqli_begin_transaction($conn);
        
        try {
            if ($new_status === 'Resolved') {
                $stmt = mysqli_prepare($conn, "DELETE FROM active_outage_table WHERE _active_outage_id_ = ?");
                mysqli_stmt_bind_param($stmt, "i", $outage_id);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "INSERT INTO resolved_outage_table (_resolved_outage_id_) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "i", $outage_id);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, 
                    "INSERT INTO notification_table 
                    (_notification_time_, _notification_title_, _notification_message_)
                    VALUES (NOW(), 'Outage Resolved', 'Utility service has been restored')");
                mysqli_stmt_execute($stmt);

                $notification_id = mysqli_insert_id($conn);

                $stmt = mysqli_prepare($conn, 
                    "INSERT INTO alert_notifiaction_table (_alt_not_id_)
                    VALUES (?)");
                mysqli_stmt_bind_param($stmt, "i", $notification_id);
                mysqli_stmt_execute($stmt);

            } elseif ($new_status === 'Active') {
                $stmt = mysqli_prepare($conn, "DELETE FROM resolved_outage_table WHERE _resolved_outage_id_ = ?");
                mysqli_stmt_bind_param($stmt, "i", $outage_id);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "INSERT INTO active_outage_table (_active_outage_id_) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "i", $outage_id);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conn);
            $_SESSION['success_message'] = "Outage status updated successfully";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Failed to update outage status: " . $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Handle outage deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteOutage'])) {
        $outage_id = intval($_POST['outage_id']);

        mysqli_begin_transaction($conn);
        
        try {
            $stmt = mysqli_prepare($conn, "DELETE FROM outage_alert_notification_table WHERE _outage_id_ = ?");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, "
                DELETE l, m, h 
                FROM outage_mapping_table om
                LEFT JOIN low_impact_table l ON om._outage_map_id_ = l._low_impact_id_
                LEFT JOIN medium_impact_table m ON om._outage_map_id_ = m._medium_impact_id_
                LEFT JOIN high_impact_table h ON om._outage_map_id_ = h._high_impact_id_
                WHERE om._outage_id_ = ?
            ");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, "DELETE FROM outage_mapping_table WHERE _outage_id_ = ?");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, "DELETE FROM active_outage_table WHERE _active_outage_id_ = ?");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, "DELETE FROM resolved_outage_table WHERE _resolved_outage_id_ = ?");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, "DELETE FROM outage_table WHERE _outage_id_ = ?");
            mysqli_stmt_bind_param($stmt, "i", $outage_id);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            $_SESSION['success_message'] = "Outage deleted successfully";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Failed to delete outage: " . $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Pagination setup
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Fetch outages with utility type
    $outageQuery = "SELECT o.*, 
        CASE 
            WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
            WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
            WHEN w._water_id_ IS NOT NULL THEN 'Water'
        END as utility_type,
        CASE
            WHEN ao._active_outage_id_ IS NOT NULL THEN 'Active'
            WHEN ro._resolved_outage_id_ IS NOT NULL THEN 'Resolved'
            ELSE 'Pending'
        END as status
        FROM outage_table o
        LEFT JOIN electricity_table e ON o._utility_id_ = e._electricity_id_
        LEFT JOIN gas_table g ON o._utility_id_ = g._gas_id_
        LEFT JOIN water_table w ON o._utility_id_ = w._water_id_
        LEFT JOIN active_outage_table ao ON o._outage_id_ = ao._active_outage_id_
        LEFT JOIN resolved_outage_table ro ON o._outage_id_ = ro._resolved_outage_id_
        WHERE o._affected_area_ LIKE ?
        ORDER BY o._start_time_ DESC
        LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $outageQuery);
    $searchParam = "%$search%";
    mysqli_stmt_bind_param($stmt, "sii", $searchParam, $itemsPerPage, $offset);
    mysqli_stmt_execute($stmt);
    $outages = mysqli_stmt_get_result($stmt);

    // Get total count for pagination
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM outage_table WHERE _affected_area_ LIKE ?");
    mysqli_stmt_bind_param($stmt, "s", $searchParam);
    mysqli_stmt_execute($stmt);
    $totalCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    $totalPages = ceil($totalCount / $itemsPerPage);

} catch (Exception $e) {
    error_log("Error in admin-outage.php: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Outage Management</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/admin-outage.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
                        <li><a href="./admin-outage.php" class="nav-link px-3 link-secondary">Outage</a></li>
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                        <li><a href="./admin-user-control" class="nav-link px-3 link-body-emphasis">Client</a></li>
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
                        <li><a href="./admin-outage.php" class="nav-link px-3 link-secondary">Outage</a></li>
                        <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                        <li><a href="./admin-notification.php" class="nav-link px-3 link-body-emphasis">Notification</a></li>
                        <li><a href="./admin-login-seasion.php" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                        <li><a href="./admin-user-control" class="nav-link px-3 link-body-emphasis">Client</a></li>
                        <li><a href="./admin-feedback.php" class="nav-link px-3 link-body-emphasis">Feedback</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']); 
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <main id="main-section">
        <h2 id="sub-div-header">Outage Map</h2>
        <div class="card mb-4">
            <div class="card-body">
                <!-- Map Search -->
                <div class="mb-3">
                    <input type="text" id="areaInput" class="form-control" placeholder="🔍 Search area" autocomplete="off">
                    <ul id="suggestions" class="list-group"></ul>
                </div>
                
                <!-- Map -->
                <div id="map"></div>
                
                <!-- Legend -->
                <div class="d-flex justify-content-center mt-3">
                    <div class="px-3" id="map-gas">■ Gas</div>
                    <div class="px-3" id="map-water">■ Water</div>
                    <div class="px-3" id="map-electricity">■ Electricity</div>
                </div>

                <!-- Outage Form -->
                <form id="outageForm" method="POST">
                    <!-- Hidden field to identify form submission -->
                    <input type="hidden" name="addOutage" value="1">
                    
                    <!-- Hidden fields for location data -->
                    <input type="hidden" name="areaName" id="areaNameInput" required>
                    <input type="hidden" name="latitude" id="latitudeInput" required>
                    <input type="hidden" name="longitude" id="longitudeInput" required>
                    
                    <div class="mt-3 small">
                        <!-- Location Info Display -->
                        <h5 class="mb-3">Selected Location</h5>
                        <p>Area: <span id="areaName">N/A</span></p>
                        <p>Latitude: <span id="latitude">N/A</span></p>
                        <p>Longitude: <span id="longitude">N/A</span></p>

                        <!-- Range Input -->
                        <div class="mb-4">
                            <h5 class="mb-2">Range (meters)</h5>
                            <input type="number" id="radiusInput" name="radiusInput" 
                                class="form-control" required min="100"
                                placeholder="Enter range in meters">
                        </div>

                        <!-- Utility Type Selection -->
                        <div class="mb-3">
                            <label for="notificationType" class="form-label">Type</label>
                            <select class="form-select" id="notificationType" name="notificationType" required>
                                <option value="Electricity">Electricity</option>
                                <option value="Gas">Gas</option>
                                <option value="Water">Water</option>
                            </select>
                        </div>

                        <!-- Impact Level Selection -->
                        <div class="mb-3">
                            <label for="impactType" class="form-label">Impact Level</label>
                            <select class="form-select" id="impactType" name="impactType" required>
                                <option value="low">Low Impact</option>
                                <option value="medium">Medium Impact</option>
                                <option value="high">High Impact</option>
                            </select>
                        </div>

                        <!-- Start Date/Time -->
                        <div class="mb-4">
                            <h5 class="mb-2">Start Time & Date</h5>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="date" id="startDate" name="startDate" 
                                        class="form-control" required>
                                </div>
                                <div class="col">
                                    <input type="time" id="startTime" name="startTime" 
                                        class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- End Date/Time -->
                        <div class="mb-4">
                            <h5 class="mb-2">End Time & Date</h5>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="date" id="endDate" name="endDate" 
                                        class="form-control" required>
                                </div>
                                <div class="col">
                                    <input type="time" id="endTime" name="endTime" 
                                        class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-center mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                Add Outage
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Outage List -->
        <div id="table-section">
            <div class="d-flex justify-content-between">
                <h2 id="sub-div-header">Outage List</h2>
                <div class="py-3">
                    <form method="GET" class="d-flex">
                        <input class="form-control me-2" type="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="🔍 Search area">
                        <button class="btn btn-outline-primary" type="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Area</th>
                                    <th>Type</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($outages)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['_outage_id_']); ?></td>
                                    <td><?php echo htmlspecialchars($row['_affected_area_']); ?></td>
                                    <td><?php echo htmlspecialchars($row['utility_type']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($row['_start_time_'])); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($row['_end_time_'])); ?></td>
                                    <td>
                                    <?php 
                                    $statusClass = '';
                                    $statusIcon = '';
                                    
                                    switch($row['status']) {
                                        case 'Active':
                                            $statusClass = 'text-bg-warning';
                                            $statusIcon = 'bi bi-exclamation-circle';
                                            break;
                                        case 'Resolved':
                                            $statusClass = 'text-bg-success';
                                            $statusIcon = 'bi bi-check-circle';
                                            break;
                                        case 'Pending':
                                            $statusClass = 'text-bg-secondary';
                                            $statusIcon = 'bi bi-clock';
                                            break;
                                        default:
                                            $statusClass = 'text-bg-secondary';
                                            $statusIcon = 'bi bi-question-circle';
                                    }
                                    ?>
                                    <span class="badge rounded-pill <?php echo $statusClass; ?>">
                                        <i class="<?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                    <td>
                                        <?php if ($row['status'] === 'Active'): ?>
                                            <form method="POST" style="display: inline;" class="me-1">
                                                <input type="hidden" name="updateStatus" value="1">
                                                <input type="hidden" name="outage_id" value="<?php echo $row['_outage_id_']; ?>">
                                                <input type="hidden" name="status" value="Resolved">
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Mark this outage as resolved?')" 
                                                        title="Mark Resolved">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($row['status'] === 'Resolved'): ?>
                                            <form method="POST" style="display: inline;" class="me-1">
                                                <input type="hidden" name="updateStatus" value="1">
                                                <input type="hidden" name="outage_id" value="<?php echo $row['_outage_id_']; ?>">
                                                <input type="hidden" name="status" value="Active">
                                                <button type="submit" class="btn btn-sm btn-warning" 
                                                        onclick="return confirm('Reactivate this outage?')"
                                                        title="Reactivate">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="deleteOutage" value="1">
                                            <input type="hidden" name="outage_id" value="<?php echo $row['_outage_id_']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this outage?')"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <!-- Previous page -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next page -->
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
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
    <script src="../js/outage.js"></script>

</body>
</html>