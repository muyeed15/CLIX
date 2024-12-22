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
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/slick.min.css">
    <link rel="stylesheet" href="../css/slick-theme.min.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<main id="main-section">
    <div class="main-container">
        <!-- hero section -->
        <section class="hero-section">
            <video class="hero-video" autoplay muted loop>
                <source src="../vid/20003753-hd_1920_1080_60fps.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <img class="hero-logo" src="../img/CLIX-white.svg" alt="Logo">
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
                            <img class="utility-svg" src="../img/gas-costs-svgrepo-com.svg" alt="">
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
                            <img class="utility-svg" src="../img/water-fee-svgrepo-com.svg">
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
                            <img class="utility-svg" src="../img/hydropower-coal-svgrepo-com.svg">
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
                </p>
            </div>
            <div class="about-box-container">
                <div class="about-box-container-1">
                    <div class="about-box">
                        <img class="about-logo" src="../img/monitoring-analytics-performance-svgrepo-com.svg">
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
                        <img class="about-logo" src="../img/web-analytics-pie-chart-svgrepo-com.svg">
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
                        <img class="about-logo" src="../img/creadit-card-debit-svgrepo-com.svg">
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
                        <img class="about-logo" src="../img/bell-svgrepo-com.svg">
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
                        <img class="profile-pic" src="../img/LinkedIn_1x1_1000px.jpg" alt="LinkedIn Profile">
                        <span class="overlay-text">Muyeed</span>
                    </div>
                    <div class="profile-container">
                        <img class="profile-pic" src="../img/dipra_1000px.jpeg" alt="Dipra">
                        <span class="overlay-text">Dipra</span>
                    </div>
                    <div class="profile-container">
                        <img class="profile-pic" src="../img/imitiaz_1000px.jpg" alt="Imitiaz">
                        <span class="overlay-text">Imtiaj</span>
                    </div>
                    <div class="profile-container">
                        <img class="profile-pic" src="../img/alam_1000px.jpeg" alt="Alam">
                        <span class="overlay-text">Alam</span>
                    </div>
                    <div class="profile-container">
                        <img class="profile-pic" src="../img/shrabon_1000px.jpeg" alt="Shrabon">
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
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/jquery.min.js"></script>
<script src="../js/slick.min.js"></script>
<script src="../js/slick.autoplay.js"></script>
</body>

</html>
