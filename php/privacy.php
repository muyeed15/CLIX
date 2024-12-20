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
    <link rel="stylesheet" href="../css/animation.css">
    <link rel="stylesheet" href="../css/privacy.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<main id="main-section">
    <div class="privacy-container">
        <div class="privacy-header">
            <h1 class="privacy-title">Privacy Policy</h1>
            <p class="privacy-subtitle">Last updated: December 08, 2024</p>
        </div>

        <div class="privacy-section">
            <h2>1. Information We Collect</h2>
            <p>At CLIX, we collect various types of information to provide and improve our services:</p>
            <ul>
                <li>Personal identification information (Name, email address, phone number)</li>
                <li>Usage data and preferences</li>
                <li>Device and connection information</li>
                <li>Location data (with your consent)</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>2. How We Use Your Information</h2>
            <p>We use the collected information for:</p>
            <ul>
                <li>Providing and maintaining our services</li>
                <li>Improving user experience</li>
                <li>Communicating updates and important notices</li>
                <li>Processing transactions</li>
                <li>Analyzing usage patterns to enhance our platform</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>3. Data Security</h2>
            <p>We implement appropriate security measures to protect your personal information, including:</p>
            <ul>
                <li>Encryption of sensitive data</li>
                <li>Regular security assessments</li>
                <li>Access controls and authentication</li>
                <li>Secure data storage practices</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>4. Data Sharing and Disclosure</h2>
            <p>We may share your information with:</p>
            <ul>
                <li>Service providers and partners</li>
                <li>Legal authorities when required by law</li>
                <li>Third parties with your explicit consent</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>5. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Request correction of inaccurate data</li>
                <li>Request deletion of your data</li>
                <li>Opt-out of marketing communications</li>
                <li>Control your privacy settings</li>
            </ul>
        </div>
    </div>
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>

</body>

</html>
