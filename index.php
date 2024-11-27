<?php
session_start();
require_once './php/db-connection.php';

if (!isset($_SESSION['nid'])) {
    header("Location: login.php");
    exit;
}

$nid = $_SESSION['nid'];

try {
    // Notification
    $notificationQuery = "SELECT * 
                        FROM (
                            SELECT * 
                            FROM notification_t 
                            WHERE (_nid_ = ? OR _nid_ IS NULL)
                            ORDER BY _date_ DESC, _time_ DESC 
                            LIMIT 10
                        ) AS _notifications_";

    $stmt = mysqli_prepare($conn, $notificationQuery);
    mysqli_stmt_bind_param($stmt, "s", $nid);
    mysqli_stmt_execute($stmt);
    $notifications = mysqli_stmt_get_result($stmt);

    // User Picture
    $pictureQuery = "SELECT _picture_
                    FROM user_t
                    WHERE _nid_ = ?";

    $stmt = mysqli_prepare($conn, $pictureQuery);
    mysqli_stmt_bind_param($stmt, "i", $nid);
    mysqli_stmt_execute($stmt);
    $picture = mysqli_stmt_get_result($stmt);

    if (($row = mysqli_fetch_assoc($picture)) && (!empty($row['_picture_']) && $row['_picture_'] !== NULL)) {
        $pictureData = $row['_picture_'];
        $base64Image = base64_encode($pictureData);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "../img/user-rounded-svgrepo-com.jpg";
    }

    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
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
    <link rel="stylesheet" href="./css/bootstrap.css">
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/slick.min.css">
    <link rel="stylesheet" href="./css/slick-theme.min.css">
</head>

<!-- body -->

<body>
    <!-- header -->
    <header class="border-bottom" id="header-section">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../index.php" class="d-flex align-items-center mb-lg-0">
                    <img src="./img/CLIX.svg" id="header-logo" alt="Logo" class="img-fluid">
                </a>
                
                <!-- Navbar -->
                <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <ul class="nav">
                        <li><a href="./" class="nav-link px-3 link-secondary">Home</a></li>
                        <li><a href="./php/dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./php/pay.php" class="nav-link px-3 link-body-emphasis">Pay Bill</a></li>
                        <li><a href="./php/outage.php" class="nav-link px-3 link-body-emphasis">Outage Area</a></li>
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
                                <li><a class="dropdown-item small" href="#"><?= htmlspecialchars($row['_message_']); ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- User Picture -->
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?php echo $imageSrc; ?>" alt="User" class="rounded-circle" style="width: 36px; height: 36px;">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="./php/profile.php">Profile</a></li>
                            <li><a class="dropdown-item small" href="./php/settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="./php/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Collapsible Mobile Menu -->
            <div class="collapse" id="mobileNav">
                <nav class="navbar-nav">
                    <ul class="nav flex-column text-center">
                        <li><a href="./" class="nav-link px-3 link-secondary">Home</a></li>
                        <li><a href="./php/dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./php/pay.php" class="nav-link px-3 link-body-emphasis">Pay Bill</a></li>
                        <li><a href="./php/outage.php" class="nav-link px-3 link-body-emphasis">Outage Area</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- main -->
    <main id="main-section">
        <div class="main-container">
            <!-- hero section -->
            <section class="hero-section">
                <video class="hero-video" autoplay muted loop>
                    <source src="./vid/3052161-hd_1920_1080_24fps.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <img class="hero-logo" src="./img/CLIX-white.svg" alt="Logo">
                <p class="hero-header">
                    Convenient<br>Living through<br>Integrated Experience
                </p>
                <input class="hero-button" type="button" value="LEARN MORE ABOUT US">
            </section>

            <!-- about section 1 -->
            <section class="about-section-1">
                <label class="about-label">All in one Platform</label>
                <div class="utility-container">
                    <!-- Gas -->
                    <div class="utility-info-container">
                        <div class="utility-info">
                            <div class="utility-svg-container">
                                <img class="utility-svg" src="./img/gas-costs-svgrepo-com.svg">
                            </div>
                            <div class="utility-label-container">
                                <p class="utility-header">Efficient Gas Usage</p>
                                <p class="utility-paragraph">Conserve gas with our practical tips, reducing costs and
                                    ensuring
                                    reliable access to fuel.
                                </p>
                                <a class="utility-link" href="#readmore">Read More</a>
                            </div>
                        </div>
                    </div>

                    <!-- Water -->
                    <div class="utility-info-container">
                        <div class="utility-info">
                            <div class="utility-svg-container">
                                <img class="utility-svg" src="./img/water-fee-svgrepo-com.svg">
                            </div>
                            <div class="utility-label-container">
                                <p class="utility-header">Water Conservation</p>
                                <p class="utility-paragraph">Adopt sustainable water-saving practices to lower
                                    consumption and
                                    protect valuable resources.
                                </p>
                                <a class="utility-link" href="#readmore">Read More</a>
                            </div>
                        </div>
                    </div>

                    <!-- Electricity -->
                    <div class="utility-info-container">
                        <div class="utility-info">
                            <div class="utility-svg-container">
                                <img class="utility-svg" src="./img/hydropower-coal-svgrepo-com.svg">
                            </div>
                            <div class="utility-label-container">
                                <p class="utility-header">Smart Electricity Use</p>
                                <p class="utility-paragraph">Optimize electricity usage with tailored recommendations to
                                    manage load
                                    shedding efficiently.
                                </p>
                                <a class="utility-link" href="#readmore">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- about section 2 -->
            <section class="about-section-2">
                <div class="about-label-container">
                    <p class="about-section-2-label">
                        We help you to<br>make living easy ->
                        <br><br>Let's<br>.make<br>..it<br>...easy!
                    </p>
                </div>
                <div class="about-box-container">
                    <div class="about-box-container-1">
                        <div class="about-box">
                            <img class="about-logo" src="./img/monitoring-analytics-performance-svgrepo-com.svg">
                            <p class="about-box-header">
                                Real-Time Resource Monitoring
                            <p>
                            <p class="about-box-paragraph">
                                Users can monitor their water, electricity, and gas usage in real time, gaining insights
                                into their consumption patterns
                                for better management.
                            </p>
                        </div>
                        <div class="about-box">
                            <img class="about-logo" src="./img/web-analytics-pie-chart-svgrepo-com.svg">
                            <p class="about-box-header">
                                Optimized Resource Use Recommendations
                            <p>
                            <p class="about-box-paragraph">
                                CLIX provides users with tips to manage electricity, gas, and water consumption.
                                Personalized load-shedding advice,
                                efficient cooking techniques, and water-saving strategies guide users toward sustainable
                                and reliable usage.
                            </p>
                        </div>
                    </div>
                    <div class="about-box-container-2">
                        <div class="about-box">
                            <img class="about-logo" src="./img/creadit-card-debit-svgrepo-com.svg">
                            <p class="about-box-header">
                                Integrated Payment System
                            <p>
                            <p class="about-box-paragraph">
                                A secure, in-app payment system allows users to pay utility bills directly through CLIX,
                                streamlining the payment
                                process and helping prevent service interruptions.
                            </p>
                        </div>
                        <div class="about-box">
                            <img class="about-logo" src="./img/bell-svgrepo-com.svg">
                            <p class="about-box-header">
                                Push Notifications
                            <p>
                            <p class="about-box-paragraph">
                                Users receive timely notifications for billing reminders, maintenance advice, and
                                personalized insights into their
                                resource usage, supporting better utility management.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- about section 3 -->
            <section class="about-section-3">
                <label class="about-label">Why choose us?</label>
                <div class="service-background">
                    <div class="service-label-container">
                        <p class="service-label-header">Assurance In Service</p>
                        <p class="service-label-paragraph">
                            An Independent Validation <br> and Testing services from SISAR. <br> Helps to reduce
                            software
                            development efforts
                        </p>
                    </div>
                </div>
            </section>

            <!-- portfolio-section -->
            <section class="portfolio-section">
                <p class="about-label">
                    Our Team
                </p>
                <div class="team-container">
                    <div class="autoplay">
                        <div class="profile-container">
                            <img class="profile-pic" src="./img/LinkedIn_1x1_1000px.jpg" alt="LinkedIn Profile">
                            <span class="overlay-text">Muyeed</span>
                        </div>
                        <div class="profile-container">
                            <img class="profile-pic" src="./img/dipra_1000px.jpeg" alt="Dipra">
                            <span class="overlay-text">Dipra</span>
                        </div>
                        <div class="profile-container">
                            <img class="profile-pic" src="./img/imitiaz_1000px.jpg" alt="Imitiaz">
                            <span class="overlay-text">Imitiaz</span>
                        </div>
                        <div class="profile-container">
                            <img class="profile-pic" src="./img/alam_1000px.jpeg" alt="Alam">
                            <span class="overlay-text">Alam</span>
                        </div>
                        <div class="profile-container">
                            <img class="profile-pic" src="./img/shrabon_1000px.jpeg" alt="Shrabon">
                            <span class="overlay-text">Shrabon</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- about section 4 -->
            <section class="about-section-4">
                <p class="about-label">
                    Achievements
                </p>
                <div class="stat-container">
                    <div class="stat-info-container">
                        <p class="stat-header">70K +</p>
                        <p class="stat-paragraph">
                            Users across Bangladesh
                        </p>
                    </div>
                    <div class="stat-info-container">
                        <p class="stat-header">64</p>
                        <p class="stat-paragraph">
                            District Availability
                        </p>
                    </div>
                    <div class="stat-info-container">
                        <p class="stat-header">15</p>
                        <p class="stat-paragraph">
                            Rewards Awarded
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>


    <!-- footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img class="footer-logo" src="./img/CLIX.svg">
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

    <!-- script -->
    <script src="./js/bootstrap.bundle.js"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/slick.min.js"></script>
    <script src="./js/slick.autoplay.js"></script>
</body>

</html>