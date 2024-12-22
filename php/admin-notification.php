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
    <link rel="stylesheet" href="../css/leaflet.css">
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_notification'])) {
    try {
        $type = mysqli_real_escape_string($conn, $_POST['notification_type']);
        $recipient = !empty($_POST['notification_recipient']) ?
            mysqli_real_escape_string($conn, $_POST['notification_recipient']) : null;
        $title = mysqli_real_escape_string($conn, $_POST['notification_header']);
        $message = mysqli_real_escape_string($conn, $_POST['notification_message']);
        mysqli_begin_transaction($conn);

        $user_id = null;
        if ($recipient) {
            $user_query = "SELECT _user_id_ FROM user_table WHERE _email_ = ?";
            $stmt = mysqli_prepare($conn, $user_query);
            mysqli_stmt_bind_param($stmt, "s", $recipient);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $user_id = $row['_user_id_'];
            } else {
                throw new Exception("Recipient email not found in the system.");
            }
        }

        $notification_query = "INSERT INTO notification_table 
            (_user_id_, _notification_time_, _notification_title_, _notification_message_)
            VALUES (?, NOW(), ?, ?)";

        $stmt = mysqli_prepare($conn, $notification_query);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $message);
        mysqli_stmt_execute($stmt);

        $notification_id = mysqli_insert_id($conn);

        $type_table = "";
        $type_column = "";

        switch ($type) {
            case "Information":
                $type_table = "information_notification_table";
                $type_column = "_info_not_id_";
                break;
            case "Alert":
                $type_table = "alert_notifiaction_table";
                $type_column = "_alt_not_id_";
                break;
            case "Reminder":
                $type_table = "reminder_notification_table";
                $type_column = "_rem_not_id_";
                break;
            default:
                throw new Exception("Invalid notification type.");
        }

        $type_query = "INSERT INTO $type_table ($type_column) VALUES (?)";
        $stmt = mysqli_prepare($conn, $type_query);
        mysqli_stmt_bind_param($stmt, "i", $notification_id);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);

        $_SESSION['success_message'] = "Notification created successfully!";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error creating notification: " . $e->getMessage();
    }

    echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
    exit();
}
?>

<?php
try {
    // Pagination setup
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $totalQuery = "SELECT COUNT(*) as total 
                   FROM notification_table n";

    if (!empty($search)) {
        $totalQuery .= " WHERE n._notification_title_ LIKE ? OR n._notification_message_ LIKE ?";
        $stmt = mysqli_prepare($conn, $totalQuery);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "ss", $searchParam, $searchParam);
    } else {
        $stmt = mysqli_prepare($conn, $totalQuery);
    }

    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $total_pages = ceil($totalRow['total'] / $itemsPerPage);
    mysqli_stmt_close($stmt);

// Update the total count query to include all search fields
    $totalQuery = "SELECT COUNT(*) as total 
               FROM notification_table n
               LEFT JOIN alert_notifiaction_table an ON n._notification_id_ = an._alt_not_id_
               LEFT JOIN information_notification_table ifn ON n._notification_id_ = ifn._info_not_id_
               LEFT JOIN reminder_notification_table rn ON n._notification_id_ = rn._rem_not_id_";

    if (!empty($search)) {
        $totalQuery .= " WHERE (n._notification_title_ LIKE ? 
                    OR n._notification_message_ LIKE ?
                    OR CASE 
                        WHEN an._alt_not_id_ IS NOT NULL THEN 'Alert'
                        WHEN ifn._info_not_id_ IS NOT NULL THEN 'Information'
                        WHEN rn._rem_not_id_ IS NOT NULL THEN 'Reminder'
                        ELSE 'Other'
                    END LIKE ?)";
        $stmt = mysqli_prepare($conn, $totalQuery);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "sss", $searchParam, $searchParam, $searchParam);
    } else {
        $stmt = mysqli_prepare($conn, $totalQuery);
    }

// Update the total count query to include recipient search
    $totalQuery = "SELECT COUNT(*) as total 
               FROM notification_table n
               LEFT JOIN alert_notifiaction_table an ON n._notification_id_ = an._alt_not_id_
               LEFT JOIN information_notification_table ifn ON n._notification_id_ = ifn._info_not_id_
               LEFT JOIN reminder_notification_table rn ON n._notification_id_ = rn._rem_not_id_
               LEFT JOIN user_table u ON n._user_id_ = u._user_id_";

    if (!empty($search)) {
        $totalQuery .= " WHERE (n._notification_title_ LIKE ? 
                    OR n._notification_message_ LIKE ?
                    OR CASE 
                        WHEN an._alt_not_id_ IS NOT NULL THEN 'Alert'
                        WHEN ifn._info_not_id_ IS NOT NULL THEN 'Information'
                        WHEN rn._rem_not_id_ IS NOT NULL THEN 'Reminder'
                        ELSE 'Other'
                    END LIKE ?
                    OR u._email_ LIKE ?
                    OR CONCAT(u._first_name_, ' ', u._last_name_) LIKE ?
                    OR CASE WHEN n._user_id_ IS NULL THEN 'Everyone' ELSE '' END LIKE ?)";
        $stmt = mysqli_prepare($conn, $totalQuery);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "ssssss",
            $searchParam, $searchParam, $searchParam,
            $searchParam, $searchParam, $searchParam);
    } else {
        $stmt = mysqli_prepare($conn, $totalQuery);
    }

    $query = "SELECT n.*, 
    CASE 
        WHEN an._alt_not_id_ IS NOT NULL THEN 'Alert'
        WHEN ifn._info_not_id_ IS NOT NULL THEN 'Information'
        WHEN rn._rem_not_id_ IS NOT NULL THEN 'Reminder'
        ELSE 'Other'
    END as notification_type,
    u._first_name_, u._last_name_, u._email_
    FROM notification_table n
    LEFT JOIN alert_notifiaction_table an ON n._notification_id_ = an._alt_not_id_
    LEFT JOIN information_notification_table ifn ON n._notification_id_ = ifn._info_not_id_
    LEFT JOIN reminder_notification_table rn ON n._notification_id_ = rn._rem_not_id_
    LEFT JOIN user_table u ON n._user_id_ = u._user_id_";

    if (!empty($search)) {
        $query .= " WHERE (n._notification_title_ LIKE ? 
                OR n._notification_message_ LIKE ?
                OR CASE 
                    WHEN an._alt_not_id_ IS NOT NULL THEN 'Alert'
                    WHEN ifn._info_not_id_ IS NOT NULL THEN 'Information'
                    WHEN rn._rem_not_id_ IS NOT NULL THEN 'Reminder'
                    ELSE 'Other'
                END LIKE ?
                OR u._email_ LIKE ?
                OR CONCAT(u._first_name_, ' ', u._last_name_) LIKE ?
                OR CASE WHEN n._user_id_ IS NULL THEN 'Everyone' ELSE '' END LIKE ?)";
        $query .= " ORDER BY n._notification_time_ DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $query);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "ssssssii",
            $searchParam, $searchParam, $searchParam,
            $searchParam, $searchParam, $searchParam,
            $itemsPerPage, $offset);
    } else {
        $query .= " ORDER BY n._notification_time_ DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $itemsPerPage, $offset);
    }

    mysqli_stmt_execute($stmt);
    $allNotifications = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}
?>

<main id="main-section">
    <h2 id="sub-div-header">Notification</h2>
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4" id="create-notification-card">
        <div class="card-body">
            <h5 class="card-title">Create Notification</h5>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="notification-type" class="form-label">Type</label>
                    <select class="form-select" id="notification-type" name="notification_type" required>
                        <option value="Information">Information</option>
                        <option value="Alert">Alert</option>
                        <option value="Reminder">Reminder</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="notification-recipient" class="form-label">Recipient Email</label>
                    <input type="email" class="form-control" id="notification-recipient"
                           name="notification_recipient" placeholder="Enter email (leave empty for all users)">
                </div>
                <div class="mb-3">
                    <label for="notification-header" class="form-label">Header</label>
                    <input type="text" class="form-control" id="notification-header"
                           name="notification_header" placeholder="Enter header" required>
                </div>
                <div class="mb-3">
                    <label for="notification-message" class="form-label">Message</label>
                    <textarea class="form-control" id="notification-message" name="notification_message"
                              rows="3" placeholder="Enter message" required></textarea>
                </div>
                <button type="submit" name="create_notification" class="btn btn-primary">Create Notification</button>
            </form>
        </div>
    </div>

    <div class="card mb-4" id="create-notification-card">
        <div class="card-body">
            <div class="mb-3">
                <input type="text" class="form-control" id="search-notifications"
                       placeholder="🔍 Search by header, message, type, recipient email, or recipient name"
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <table class="table table-borderless small" id="notifications-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Recipient</th>
                    <th>Header</th>
                    <th>Message</th>
                    <th>Time</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody id="notifications-table-body">
                <?php while ($row = mysqli_fetch_assoc($allNotifications)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['_notification_id_']); ?></td>
                        <td><?php echo htmlspecialchars($row['notification_type']); ?></td>
                        <td><?php
                            if ($row['_user_id_'] === null) {
                                echo "Everyone";
                            } else {
                                echo htmlspecialchars($row['_first_name_'] . ' ' . $row['_last_name_']) . '<br>' .
                                    '<small class="text-muted">' . htmlspecialchars($row['_email_']) . '</small>';
                            }
                            ?></td>
                        <td><?php echo htmlspecialchars($row['_notification_title_']); ?></td>
                        <td><?php echo htmlspecialchars($row['_notification_message_']); ?></td>
                        <td><?php echo date('H:i', strtotime($row['_notification_time_'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['_notification_time_'])); ?></td>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-notifications');
        const notificationsTable = document.getElementById('notifications-table');

        // Function to perform search
        function performSearch() {
            const searchQuery = searchInput.value.trim();

            // Update URL with search parameter
            const url = new URL(window.location.href);
            if (searchQuery) {
                url.searchParams.set('search', searchQuery);
            } else {
                url.searchParams.delete('search');
            }

            // Reset to first page when searching
            url.searchParams.set('page', '1');

            // Redirect to the new URL
            window.location.href = url.toString();
        }

        // Listen for Enter key press
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission if within a form
                performSearch();
            }
        });

        // If there's a search query in the URL, scroll to the table on page load
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) {
            notificationsTable.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
</script>

</body>

</html>
