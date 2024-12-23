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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

<!-- main -->

<main id="main-section">
    <h2 id="sub-div-header">User List</h2>

    <?php
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $itemsPerPage = 10;
        $offset = ($page - 1) * $itemsPerPage;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Modified count query to include banned status
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

        // Modified main query to include banned status
        $userQuery = "SELECT u.*, 
                      CONCAT(u._first_name_, ' ', u._last_name_) as full_name,
                      u._profile_picture_,
                      u._email_,
                      u._current_address_,
                      u._nid_,
                      CASE WHEN b._banned_client_id_ IS NOT NULL THEN 1 ELSE 0 END as is_banned
                      FROM user_table u
                      INNER JOIN client_table c ON u._user_id_ = c._client_id_
                      LEFT JOIN banned_client_table b ON c._client_id_ = b._banned_client_id_";

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
        ?>

        <div class="card mb-4" id="user-login-card">
            <div class="card-body">
                <div class="mb-4">
                    <label for="search-users" class="form-label">Search User</label>
                    <input type="text" class="form-control" id="search-users"
                           placeholder="🔍 Search by Name, NID, or Address"
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>

                <table class="table table-borderless small" id="user-login-table">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Status</th>
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
                            <td>
                                <?php if ($user['is_banned']): ?>
                                    <span class="d-flex align-items-center">
                                    <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                    <span class="text-danger">Banned</span>
                                </span>
                                <?php else: ?>
                                    <span class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    <span class="text-success">Active</span>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="./admin-user-edit.php?id=<?php echo $user['_user_id_']; ?>">
                                    <img id="edit-svg" src="../img/edit-svgrepo-com.svg">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>

                <?php require_once './pagination.php'; ?>
            </div>
        </div>

        <?php
    } catch (Exception $e) {
        echo "Error fetching data: " . $e->getMessage();
    }
    ?>
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
