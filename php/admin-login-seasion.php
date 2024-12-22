<?php
global $conn;
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
    <title>CLIX: Convenient Living & Integrated Experience</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/admin-base.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

<!-- main -->
<?php
try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Pagination
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
    $total_pages = ceil($totalRow['total'] / $itemsPerPage);
    mysqli_stmt_close($stmt);

    // Query
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

        <!-- pagination -->
        <?php
        require_once './pagination.php';
        ?>
    </div>
</main>

<!-- footer -->
<?php
require_once './admin-footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
