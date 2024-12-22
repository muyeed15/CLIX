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
    <link rel="stylesheet" href="../css/admin-feedback.css">
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
    // Fetch Feedbacks with Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Build the query
    $feedbackQuery = "SELECT f.*, u._first_name_, u._last_name_, u._email_
                     FROM feedback_table f
                     LEFT JOIN user_table u ON f._user_id_ = u._user_id_";

    if (!empty($search)) {
        $feedbackQuery .= " WHERE f._feedback_name_ LIKE ? OR f._feedback_subject_ LIKE ?";
        $searchParam = "%$search%";
        $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM (" . $feedbackQuery . ") as count_table");
        mysqli_stmt_bind_param($countStmt, "ss", $searchParam, $searchParam);
    } else {
        $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM (" . $feedbackQuery . ") as count_table");
    }

    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalFeedbacks = mysqli_fetch_row($countResult)[0];
    $totalPages = ceil($totalFeedbacks / $itemsPerPage);

    // Add pagination to the main query
    $feedbackQuery .= " ORDER BY f._feedback_time_ DESC LIMIT ? OFFSET ?";

    if (!empty($search)) {
        $stmt = mysqli_prepare($conn, $feedbackQuery);
        mysqli_stmt_bind_param($stmt, "ssii", $searchParam, $searchParam, $itemsPerPage, $offset);
    } else {
        $stmt = mysqli_prepare($conn, $feedbackQuery);
        mysqli_stmt_bind_param($stmt, "ii", $itemsPerPage, $offset);
    }

    mysqli_stmt_execute($stmt);
    $feedbacks = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}
?>

<main id="main-section">
    <h2 id="sub-div-header">Feedback Management</h2>

    <div class="card mb-4">
        <div class="card-body">
            <!-- Search Form -->
            <div class="mb-4">
                <form method="GET" action="" class="d-flex">
                    <input type="text" id="search-feedback" name="search" class="form-control"
                           placeholder="Search by name or subject"
                           value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </form>
            </div>

            <!-- Feedback List -->
            <div class="feedback-list">
                <?php if (mysqli_num_rows($feedbacks) > 0): ?>
                    <?php while ($feedback = mysqli_fetch_assoc($feedbacks)): ?>
                        <div class="card feedback-card mb-3"
                             onclick="window.location.href='admin-message.php?id=<?php echo $feedback['_feedback_id_']; ?>'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($feedback['_feedback_subject_']); ?></h5>
                                        <p class="text-muted mb-2">
                                            From: <?php echo htmlspecialchars($feedback['_feedback_name_']); ?>
                                            <?php if ($feedback['_email_']): ?>
                                                (<?php echo htmlspecialchars($feedback['_email_']); ?>)
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y g:i A', strtotime($feedback['_feedback_time_'])); ?>
                                    </small>
                                </div>
                                <p class="card-text message-preview">
                                    <?php echo htmlspecialchars($feedback['_feedback_message_']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <!-- Pagination -->
                    <?php if ($totalPages > 0): ?>
                        <div class="d-flex justify-content-center mb-3"
                             id="pagination-section" <?php echo ($totalPages <= 1) ? 'style="display: none !important;"' : ''; ?>>
                            <nav aria-label="Page navigation">
                                <ul class="pagination no-border">
                                    <?php
                                    if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="?page=<?php echo($page - 1); ?>&search=<?php echo urlencode($search); ?>"
                                               aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif;

                                    $start_page = max(1, min($page - 2, $totalPages - 4));
                                    $end_page = min($totalPages, $start_page + 4);

                                    if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="?page=1&search=<?php echo urlencode($search); ?>">1</a>
                                        </li>
                                        <?php if ($start_page > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif;
                                    endif;

                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link"
                                               href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor;

                                    if ($end_page < $totalPages): ?>
                                        <?php if ($end_page < $totalPages - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>"><?php echo $totalPages; ?></a>
                                        </li>
                                    <?php endif;

                                    if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="?page=<?php echo($page + 1); ?>&search=<?php echo urlencode($search); ?>"
                                               aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <p class="text-muted">No feedback messages found.</p>
                    </div>
                <?php endif; ?>
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

</body>

</html>
