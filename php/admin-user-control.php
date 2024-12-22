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
    <link rel="stylesheet" href="../css/admin-outage.css">
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
    // Pagination setup
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Search parameter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Count total records
    $countQuery = "SELECT COUNT(*) as total 
                   FROM user_table u 
                   INNER JOIN client_table c ON u._user_id_ = c._client_id_";

    if (!empty($search)) {
        $countQuery .= " WHERE _first_name_ LIKE ? 
                        OR _last_name_ LIKE ?
                        OR _nid_ LIKE ?
                        OR _current_address_ LIKE ?
                        OR _email_ LIKE ?";
        $stmt = mysqli_prepare($conn, $countQuery);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "sssss", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
    } else {
        $stmt = mysqli_prepare($conn, $countQuery);
    }

    mysqli_stmt_execute($stmt);
    $countResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($countResult);
    $total_pages = ceil($totalRow['total'] / $itemsPerPage);
    mysqli_stmt_close($stmt);

    // Main query to fetch users
    $userQuery = "SELECT u.*, 
                  CONCAT(u._first_name_, ' ', u._last_name_) as full_name,
                  u._profile_picture_,
                  u._email_,
                  u._current_address_,
                  u._nid_
                  FROM user_table u
                  INNER JOIN client_table c ON u._user_id_ = c._client_id_";

    if (!empty($search)) {
        $userQuery .= " WHERE _first_name_ LIKE ? 
                       OR _last_name_ LIKE ?
                       OR _nid_ LIKE ?
                       OR _current_address_ LIKE ?
                       OR _email_ LIKE ?";
        $userQuery .= " ORDER BY u._user_id_ LIMIT ? OFFSET ?";

        $stmt = mysqli_prepare($conn, $userQuery);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "sssssii", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
    } else {
        $userQuery .= " ORDER BY u._user_id_ LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $userQuery);
        mysqli_stmt_bind_param($stmt, "ii", $itemsPerPage, $offset);
    }

    mysqli_stmt_execute($stmt);
    $users = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}
?>
<main id="main-section">
    <h2 id="sub-div-header">User List</h2>

    <div class="card mb-4" id="user-login-card">
        <div class="card-body">
            <!-- Search Box -->
            <div class="mb-4">
                <label for="search-users" class="form-label">Search User</label>
                <input type="text" class="form-control" id="search-users"
                       placeholder="🔍 Search by Name, NID, or Address"
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>

            <!-- User Table -->
            <table class="table table-borderless small" id="user-login-table">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Edit</th>
                </tr>
                </thead>
                <tbody id="user-login-table-body">
                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td>
                            <?php
                            if (!empty($user['_profile_picture_'])) {
                                $base64Image = base64_encode($user['_profile_picture_']);
                                $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
                            } else {
                                $imageSrc = "../img/user-rounded-svgrepo-com.jpg";
                            }
                            ?>
                            <img src="<?php echo $imageSrc; ?>" alt="User Picture" width="32" height="32"
                                 class="rounded-circle">
                        </td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['_email_']); ?></td>
                        <td><?php echo htmlspecialchars($user['_current_address_']); ?></td>
                        <td class="d-flex">
                            <a href="./admin-user-edit.php?id=<?php echo $user['_user_id_']; ?>">
                                <img id="edit-svg" src="../img/edit-svgrepo-com.svg">
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <!-- pagination -->
            <?php
            require_once './pagination.php';
            ?>
        </div>
    </div>
</main>

<!-- footer -->
<?php
require_once './admin-footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/admin-user-control.js"></script>

</body>

</html>
