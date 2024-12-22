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
    <link rel="stylesheet" href="../css/term.css">
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
    <div class="terms-container">
        <div class="terms-header">
            <h1 class="terms-title">Terms & Conditions</h1>
            <p class="terms-subtitle">Last updated: December 08, 2024</p>
        </div>

        <div class="terms-section">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using CLIX's services, you acknowledge that you have read, understood, and agree to be
                bound by these Terms & Conditions. If you do not agree to these terms, please do not use our
                services.</p>
            <div class="terms-highlight">
                <p>These terms apply to all users, visitors, and others who access or use CLIX's services.</p>
            </div>
        </div>

        <div class="terms-section">
            <h2>2. User Accounts</h2>
            <p>When creating an account with CLIX, you agree to:</p>
            <ul>
                <li>Provide accurate and complete registration information</li>
                <li>Maintain the security of your account credentials</li>
                <li>Promptly update any changes to your account information</li>
                <li>Accept responsibility for all activities that occur under your account</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>3. Service Usage</h2>
            <p>Our services are provided under the following conditions:</p>
            <ul>
                <li>Usage must comply with all applicable laws and regulations</li>
                <li>Services may not be used for any illegal or unauthorized purpose</li>
                <li>Users must not interfere with or disrupt the service infrastructure</li>
                <li>Automated access to services must be pre-approved by CLIX</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>4. Intellectual Property</h2>
            <p>All content, features, and functionality of CLIX services are protected by:</p>
            <ul>
                <li>Copyright laws</li>
                <li>Trademark rights</li>
                <li>Other intellectual property rights</li>
            </ul>
            <div class="terms-highlight">
                <p>Users may not copy, modify, distribute, or create derivative works without explicit permission from
                    CLIX.</p>
            </div>
        </div>

        <div class="terms-section">
            <h2>5. Payment Terms</h2>
            <p>For paid services, users agree to:</p>
            <ul>
                <li>Provide valid payment information</li>
                <li>Pay all fees at the time they are due</li>
                <li>Accept automatic renewal terms where applicable</li>
                <li>Review and understand the refund policy</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>6. Limitation of Liability</h2>
            <p>CLIX shall not be liable for:</p>
            <ul>
                <li>Indirect, incidental, or consequential damages</li>
                <li>Loss of data or service interruptions</li>
                <li>Third-party actions or content</li>
                <li>Events beyond our reasonable control</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>7. Modifications to Terms</h2>
            <p>CLIX reserves the right to modify these terms at any time. Users will be notified of significant changes
                through:</p>
            <ul>
                <li>Email notifications</li>
                <li>Service announcements</li>
                <li>Website updates</li>
            </ul>
            <div class="terms-highlight">
                <p>Continued use of CLIX services after changes constitutes acceptance of the modified terms.</p>
            </div>
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
