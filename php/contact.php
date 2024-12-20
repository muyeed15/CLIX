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
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="stylesheet" href="../css/slick.min.css">
    <link rel="stylesheet" href="../css/slick-theme.min.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<video autoplay muted loop id="video-background">
    <source src="../vid/12742302_1920_1080_30fps.mp4" type="video/mp4">
</video>

<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<?php
// POST feedback
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    try {
        $sql = "INSERT INTO feedback_table (_user_id_, _feedback_time_, _feedback_name_, _feedback_subject_, _feedback_message_) 
                VALUES (?, NOW(), ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "isss", $user_id, $name, $subject, $message);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const successModal = new bootstrap.Modal(document.getElementById('feedbackSuccessModal'));
                    successModal.show();
                });
            </script>";
        } else {
            throw new Exception("Error submitting feedback: " . mysqli_error($conn));
        }

        mysqli_stmt_close($stmt);

    } catch (Exception $e) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const errorModal = new bootstrap.Modal(document.getElementById('feedbackErrorModal'));
                document.getElementById('errorMessage').textContent = '" . addslashes($e->getMessage()) . "';
                errorModal.show();
            });
        </script>";
    }
}
?>

<main id="main-section">
    <section class="contact-section">
        <h1 class="section-title">Contact Us</h1>

        <!-- Contact Information -->
        <div class="contact-info mb-5">
            <div class="contact-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="contact-icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <div>
                    <h5 class="mb-0">Our Location</h5>
                    <p class="mb-0">Dhaka, Bangladesh</p>
                </div>
            </div>

            <div class="contact-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="contact-icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <div>
                    <h5 class="mb-0">Email Us</h5>
                    <a href="mailto:clix@mail.com" class="text-decoration-none text-muted">clix@mail.com</a>
                </div>
            </div>

            <div class="contact-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="contact-icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <div>
                    <h5 class="mb-0">Call Us</h5>
                    <p class="mb-0">+8801712345678</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Your Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" name="feedback_submit" class="btn w-100"
                        style="background-color: #63ba5d; color: white; border: none;">Send Message
                </button>
            </form>
        </div>
    </section>
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- Success Modal -->
<div class="modal fade" id="feedbackSuccessModal" tabindex="-1" aria-labelledby="feedbackSuccessModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #63ba5d; color: white;">
                <h5 class="modal-title" id="feedbackSuccessModalLabel">Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#63ba5d"
                     class="bi bi-check-circle-fill mb-4" viewBox="0 0 16 16">
                    <path
                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <h4>Thank you for your feedback!</h4>
                <p class="text-muted mb-0">We appreciate you taking the time to share your thoughts with us.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn px-4" style="background-color: #63ba5d; color: white;"
                        data-bs-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="feedbackErrorModal" tabindex="-1" aria-labelledby="feedbackErrorModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="feedbackErrorModalLabel">Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor"
                     class="bi bi-exclamation-circle-fill text-danger mb-4" viewBox="0 0 16 16">
                    <path
                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                </svg>
                <h4>Oops! Something went wrong</h4>
                <p class="text-muted mb-0" id="errorMessage"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- footer -->
<?php
require_once './footer.php';
?>

<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
