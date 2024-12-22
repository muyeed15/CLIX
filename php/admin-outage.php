<?php
global $conn;
ob_start();
session_start();
require_once './db-connection.php';
?>

<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Outage Management</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/admin-outage.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

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

<?php
// Helper function for redirects
function redirect($message, $type = 'success')
{
    $_SESSION[$type . '_message'] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Process all form submissions before any HTML output
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle outage form submission
        // Handle outage form submission
        if (isset($_POST['addOutage'])) {
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

                // Find affected IoT devices within range (but don't require them)
                $affected_iot_query = "SELECT i._iot_id_
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

                // Create a default mapping even if no IoT devices are affected
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO outage_mapping_table (_outage_id_, _iot_id_)
            VALUES (?, NULL)");
                mysqli_stmt_bind_param($stmt, "i", $outage_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to create default outage mapping");
                }

                $mapping_id = mysqli_insert_id($conn);
                $impact_type = strtolower($_POST['impactType']);

                // Determine which impact table to use
                $impact_table = match ($impact_type) {
                    'low' => 'low_impact_table',
                    'medium' => 'medium_impact_table',
                    'high' => 'high_impact_table',
                    default => throw new Exception("Invalid impact type specified"),
                };

                // Insert into the appropriate impact table
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO " . $impact_table . " (_" . $impact_type . "_impact_id_) 
                            VALUES (?)");

                if (!$stmt) {
                    throw new Exception("Failed to prepare impact statement");
                }

                mysqli_stmt_bind_param($stmt, "i", $mapping_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to insert impact level: " . mysqli_error($conn));
                }

                // If there are any IoT devices found, create additional mappings for them
                while ($iot = mysqli_fetch_assoc($affected_iots)) {
                    $stmt = mysqli_prepare($conn,
                        "INSERT INTO outage_mapping_table (_outage_id_, _iot_id_)
                VALUES (?, ?)");
                    mysqli_stmt_bind_param($stmt, "ii", $outage_id, $iot['_iot_id_']);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Failed to create IoT outage mapping");
                    }

                    $iot_mapping_id = mysqli_insert_id($conn);

                    // Insert into the appropriate impact table for IoT mapping
                    $stmt = mysqli_prepare($conn,
                        "INSERT INTO " . $impact_table . " (_" . $impact_type . "_impact_id_) 
                                VALUES (?)");
                    mysqli_stmt_bind_param($stmt, "i", $iot_mapping_id);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Failed to insert IoT impact level");
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
                redirect("Outage successfully recorded");

            } catch (Exception $e) {
                mysqli_rollback($conn);
                redirect("Failed to record outage: " . $e->getMessage(), 'error');
            }
        }

        // Handle status updates
        if (isset($_POST['updateStatus'])) {
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
                redirect("Outage status updated successfully");

            } catch (Exception $e) {
                mysqli_rollback($conn);
                redirect("Failed to update outage status: " . $e->getMessage(), 'error');
            }
        }

        // Handle outage deletion
        if (isset($_POST['deleteOutage'])) {
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
                redirect("Outage deleted successfully");

            } catch (Exception $e) {
                mysqli_rollback($conn);
                redirect("Failed to delete outage: " . $e->getMessage(), 'error');
            }
        }
    }

    // Pagination setup
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Get total count for pagination with search condition
    $countQuery = "SELECT COUNT(*) as total FROM outage_table WHERE _affected_area_ LIKE ?";
    $stmt = mysqli_prepare($conn, $countQuery);
    $searchParam = "%$search%";
    mysqli_stmt_bind_param($stmt, "s", $searchParam);
    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $total_pages = ceil($totalRow['total'] / $itemsPerPage);
    mysqli_stmt_close($stmt);

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
        END as status,
        CASE
            WHEN l._low_impact_id_ IS NOT NULL THEN 'Low'
            WHEN m._medium_impact_id_ IS NOT NULL THEN 'Medium'
            WHEN h._high_impact_id_ IS NOT NULL THEN 'High'
        END as impact_level,
        (_range_km_ * 1000) as range_meters
    FROM outage_table o
    LEFT JOIN electricity_table e ON o._utility_id_ = e._electricity_id_
    LEFT JOIN gas_table g ON o._utility_id_ = g._gas_id_
    LEFT JOIN water_table w ON o._utility_id_ = w._water_id_
    LEFT JOIN active_outage_table ao ON o._outage_id_ = ao._active_outage_id_
    LEFT JOIN resolved_outage_table ro ON o._outage_id_ = ro._resolved_outage_id_
    LEFT JOIN outage_mapping_table om ON o._outage_id_ = om._outage_id_
    LEFT JOIN low_impact_table l ON om._outage_map_id_ = l._low_impact_id_
    LEFT JOIN medium_impact_table m ON om._outage_map_id_ = m._medium_impact_id_
    LEFT JOIN high_impact_table h ON om._outage_map_id_ = h._high_impact_id_
    WHERE o._affected_area_ LIKE ?
    ORDER BY o._start_time_ DESC
    LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $outageQuery);
    $searchParam = "%$search%";
    mysqli_stmt_bind_param($stmt, "sii", $searchParam, $itemsPerPage, $offset);
    mysqli_stmt_execute($stmt);
    $outages = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    error_log("Error in admin-outage.php: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred. Please try again later.";
}
?>

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
                            <th>Impact</th>
                            <th>Range</th>
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

                                    switch ($row['status']) {
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
                                    <?php
                                    $impactClass = '';
                                    $impactIcon = '';

                                    switch ($row['impact_level']) {
                                        case 'High':
                                            $impactClass = 'text-bg-danger';
                                            $impactIcon = 'bi bi-exclamation-triangle';
                                            break;
                                        case 'Medium':
                                            $impactClass = 'text-bg-warning';
                                            $impactIcon = 'bi bi-exclamation';
                                            break;
                                        case 'Low':
                                            $impactClass = 'text-bg-info';
                                            $impactIcon = 'bi bi-info-circle';
                                            break;
                                        default:
                                            $impactClass = 'text-bg-secondary';
                                            $impactIcon = 'bi bi-dash-circle';
                                    }
                                    ?>
                                    <span class="badge rounded-pill <?php echo $impactClass; ?>">
                                        <i class="<?php echo $impactIcon; ?> me-1"></i>
                                        <?php echo htmlspecialchars($row['impact_level'] ?? 'Unknown'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill text-bg-secondary">
                                        <i class="bi bi-arrows-expand me-1"></i>
                                        <?php echo number_format($row['range_meters']); ?>m
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'Active'): ?>
                                        <form method="POST" style="display: inline;" class="me-1">
                                            <input type="hidden" name="updateStatus" value="1">
                                            <input type="hidden" name="outage_id"
                                                   value="<?php echo $row['_outage_id_']; ?>">
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
                                            <input type="hidden" name="outage_id"
                                                   value="<?php echo $row['_outage_id_']; ?>">
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
                                        <input type="hidden" name="outage_id"
                                               value="<?php echo $row['_outage_id_']; ?>">
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

                <!-- pagination -->
                <?php
                require_once './pagination.php';
                ?>
            </div>
        </div>
    </div>
</main>

<!-- footer -->
<?php
require_once './admin-footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/leaflet.js"></script>
<script src="../js/outage.js"></script>

</body>
</html>
