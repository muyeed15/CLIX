<?php
session_start();
require_once './db-connection.php';

$isLoggedIn = isset($_SESSION['_user_id_']);
$notifications = [];
$imageSrc = "";

$clientCheckQuery = "SELECT c._client_id_ 
                    FROM client_table c 
                    WHERE c._client_id_ = ?";

if ($isLoggedIn) {
    $user_id = $_SESSION['_user_id_'];
    
    try {
        $stmt = mysqli_prepare($conn, $clientCheckQuery);
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
            $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
        }

        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        echo "Error fetching data: " . $e->getMessage();
    }
}

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
                    var successModal = new bootstrap.Modal(document.getElementById('feedbackSuccessModal'));
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
                var errorModal = new bootstrap.Modal(document.getElementById('feedbackErrorModal'));
                document.getElementById('errorMessage').textContent = '" . addslashes($e->getMessage()) . "';
                errorModal.show();
            });
        </script>";
    }
}

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
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <?php if (!$isLoggedIn): ?>
                            <li><a href="./about.php" class="nav-link px-3 link-body-emphasis">About Us</a></li>
                            <li><a href="./contact.php" class="nav-link px-3 link-secondary">Contact Us</a></li>
                        <?php else: ?>
                            <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                            <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                            <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                        <?php endif; ?>
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

                    <?php if ($isLoggedIn): ?>
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
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <?php if (!$isLoggedIn): ?>
                            <li><a href="./about.php" class="nav-link px-3 link-body-emphasis">About Us</a></li>
                            <li><a href="./contact.php" class="nav-link px-3 link-secondary">Contact Us</a></li>
                        <?php else: ?>
                            <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                            <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                            <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main id="main-section">
        <section class="contact-section">
            <h1 class="section-title">Contact Us</h1>

            <!-- Contact Information -->
            <div class="contact-info mb-5">
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <div>
                        <h5 class="mb-0">Our Location</h5>
                        <p class="mb-0">Dhaka, Bangladesh</p>
                    </div>
                </div>

                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <div>
                        <h5 class="mb-0">Email Us</h5>
                        <a href="mailto:clix@mail.com" class="text-decoration-none text-muted">clix@mail.com</a>
                    </div>
                </div>

                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
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
                     <button type="submit" name="feedback_submit" class="btn w-100" style="background-color: #63ba5d; color: white; border: none;">Send Message</button>
                </form>
            </div>
        </section>
    </main>

    <!-- footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img class="footer-logo" src="../img/CLIX.svg">
                <small class="d-block mb-3 text-body-secondary">©2024</small>
                <p class="small text-body-secondary">
                    Why CLIX?<br>
                    Convenient Living<br>
                    Integrated Experience
                </p>
            </div>
            <div class="col-3">
                <h5>Links</h5>
                <ul class=" list-unstyled">
                    <li><a class="link-secondary text-decoration-none small" href="./about.php">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./contact.php">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./privacy.php">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./term.php">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./faq.php">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="mailto:clix@mail.com">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="">Phone: +8801712345678</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Success Modal -->
    <div class="modal fade" id="feedbackSuccessModal" tabindex="-1" aria-labelledby="feedbackSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #63ba5d; color: white;">
                    <h5 class="modal-title" id="feedbackSuccessModalLabel">Success!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#63ba5d" class="bi bi-check-circle-fill mb-4" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <h4>Thank you for your feedback!</h4>
                    <p class="text-muted mb-0">We appreciate you taking the time to share your thoughts with us.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn px-4" style="background-color: #63ba5d; color: white;" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="feedbackErrorModal" tabindex="-1" aria-labelledby="feedbackErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="feedbackErrorModalLabel">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-circle-fill text-danger mb-4" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
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

    <!-- script -->
    <script>

    </script>
    <script src="../js/bootstrap.bundle.js"></script>

</body>

</html>