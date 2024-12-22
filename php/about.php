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
    <link rel="stylesheet" href="../css/about.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>About CLIX</h1>
            <p>Revolutionizing utility management through convenient living and integrated experiences.</p>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="vision-section">
        <div class="vision-container">
            <div class="vision-card">
                <div class="vision-icon">🎯</div>
                <h3>Our Mission</h3>
                <p>To simplify utility management by providing innovative solutions that enhance everyday living through
                    smart technology and seamless integration.</p>
            </div>
            <div class="vision-card">
                <div class="vision-icon">👁️</div>
                <h3>Our Vision</h3>
                <p>Creating a future where managing utilities is effortless, sustainable, and integrated into the fabric
                    of modern living.</p>
            </div>
            <div class="vision-card">
                <div class="vision-icon">💡</div>
                <h3>Our Values</h3>
                <p>Innovation, sustainability, and user-centric solutions drive everything we do, ensuring we deliver
                    excellence in every interaction.</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="features-container">
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <p>Real-time monitoring of water, electricity, and gas usage</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔔</div>
                    <p>Smart notifications for utility outages and maintenance</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💳</div>
                    <p>Integrated bill payment system for all utilities</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🗺️</div>
                    <p>Interactive outage mapping for better planning</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <p>User-friendly interface for effortless management</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-number">24/7</div>
                    <div class="stats-label">Monitoring</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number">100%</div>
                    <div class="stats-label">Reliable</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Goal Section -->
    <section class="goal-section">
        <div class="goal-content">
            <h2 class="section-title">Our Goal</h2>
            <p>CLIX aims to transform how people manage their utilities by providing an integrated, user-friendly
                platform that promotes sustainable living while saving time and resources. Through innovative technology
                and thoughtful design, we're creating a future where utility management is seamless, efficient, and
                environmentally conscious.</p>
        </div>
    </section>
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
