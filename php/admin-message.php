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
    <link rel="stylesheet" href="../css/admin-message.css">
</head>

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

<!-- main -->
<?php
$feedback_id = $_GET['id'];

try {
    // Check if user is admin
    $clientCheckQuery = "SELECT a._admin_id_ 
                        FROM admin_table a 
                        WHERE a._admin_id_ = ?";

    $stmt = mysqli_prepare($conn, $clientCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        header("Location: access-denied.php");
        exit;
    }

    // Get feedback details
    $feedbackQuery = "SELECT f.*, u._first_name_, u._last_name_, u._email_
                     FROM feedback_table f
                     LEFT JOIN user_table u ON f._user_id_ = u._user_id_
                     WHERE f._feedback_id_ = ?";

    $stmt = mysqli_prepare($conn, $feedbackQuery);
    mysqli_stmt_bind_param($stmt, "i", $feedback_id);
    mysqli_stmt_execute($stmt);
    $feedback = mysqli_stmt_get_result($stmt);
    $feedbackData = mysqli_fetch_assoc($feedback);

    if (!$feedbackData) {
        header("Location: admin-feedback.php");
        exit;
    }

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}
?>

<main id="main-section">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <a href="admin-feedback.php" class="back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                             class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                  d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                        </svg>
                        Back to Feedback List
                    </a>
                </div>

                <div class="feedback-header">
                    <h2 class="h4 mb-3"><?php echo htmlspecialchars($feedbackData['_feedback_subject_']); ?></h2>
                    <div class="metadata mb-4">
                        <div class="sender mb-1">
                            From: <strong><?php echo htmlspecialchars($feedbackData['_feedback_name_']); ?></strong>
                            <?php if ($feedbackData['_email_']): ?>
                                &lt;<?php echo htmlspecialchars($feedbackData['_email_']); ?>&gt;
                            <?php endif; ?>
                        </div>
                        <div class="timestamp">
                            Submitted
                            on <?php echo date('F j, Y \a\t g:i A', strtotime($feedbackData['_feedback_time_'])); ?>
                        </div>
                    </div>
                </div>

                <div class="feedback-content">
                    <?php echo nl2br(htmlspecialchars($feedbackData['_feedback_message_'])); ?>
                </div>
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
