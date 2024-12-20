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
    <link rel="stylesheet" href="../css/faq.css">
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
    <div class="faq-container">
        <div class="faq-header">
            <h1 class="faq-title">Frequently Asked Questions</h1>
            <p class="faq-subtitle">Learn about CLIX web platform and smart utility management</p>
        </div>

        <div class="faq-categories">
            <button class="category-btn active" data-category="general">General</button>
            <button class="category-btn" data-category="meters">Smart Meters</button>
            <button class="category-btn" data-category="platform">Platform Usage</button>
            <button class="category-btn" data-category="technical">Technical Support</button>
        </div>

        <div class="faq-section" data-category="general">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What type of equipment do I need to use CLIX?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>To use CLIX, you'll need:</p>
                    <ul>
                        <li>CLIX-compatible smart meters for utilities you want to monitor</li>
                        <li>Internet connection</li>
                        <li>A computer or device with a modern web browser</li>
                        <li>IoT gateway device (provided with meter installation)</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How much does the smart meter installation cost?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Smart meter costs vary by type:</p>
                    <ul>
                        <li>Electricity Smart Meter: 12,000-15,000 BDT</li>
                        <li>Water Smart Meter: 8,000-10,000 BDT</li>
                        <li>Gas Smart Meter: 15,000-18,000 BDT</li>
                        <li>IoT Gateway Device: Included with your first meter</li>
                    </ul>
                    <p>Installation is free for the first meter. Additional meter installations cost 1,000 BDT each.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I access CLIX from multiple computers?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, you can access your CLIX dashboard from any computer with an internet connection. Simply log
                        in to our website using your credentials. For security, we'll notify you when your account is
                        accessed from a new device.</p>
                </div>
            </div>
        </div>

        <div class="faq-section hidden" data-category="meters">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What are CLIX Smart Meters and how do they work?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>CLIX Smart Meters are IoT-enabled utility meters that provide real-time consumption data. They
                        work by:</p>
                    <ul>
                        <li>Continuously measuring utility consumption with digital sensors</li>
                        <li>Transmitting data wirelessly to your IoT gateway</li>
                        <li>Sending encrypted data to CLIX servers for analysis</li>
                        <li>Providing real-time readings on your dashboard</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>What happens if there's a power outage?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Our smart meters include backup batteries that last up to 72 hours. They store data locally
                        during outages and sync automatically when power returns. The IoT gateway also has a 24-hour
                        backup power supply to maintain connectivity.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How accurate are the smart meters?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>CLIX Smart Meters are highly accurate with:</p>
                    <ul>
                        <li>Electricity: ±0.5% accuracy (exceeds national standards)</li>
                        <li>Water: ±1% accuracy with ultrasonic measurement</li>
                        <li>Gas: ±0.75% accuracy with digital flow sensing</li>
                    </ul>
                    <p>All meters are certified by Bangladesh Standards and Testing Institution (BSTI).</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do smart meters emit harmful radiation?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>No, CLIX Smart Meters use low-power wireless technology (LoRaWAN) that emits less radiation than
                        a mobile phone. They are certified safe by international standards and the Bangladesh
                        Telecommunication Regulatory Commission.</p>
                </div>
            </div>
        </div>

        <div class="faq-section hidden" data-category="platform">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I view my real-time consumption data?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>On your CLIX dashboard, you'll find:</p>
                    <ul>
                        <li>Live consumption meters for each utility</li>
                        <li>Interactive graphs showing usage patterns</li>
                        <li>Cost projections based on current usage</li>
                        <li>Comparative analysis with previous periods</li>
                    </ul>
                    <p>Data updates every 30 seconds for electricity and every 5 minutes for water and gas.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I export my consumption data?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, you can export data in several formats:</p>
                    <ul>
                        <li>CSV for spreadsheet analysis</li>
                        <li>PDF for monthly reports</li>
                        <li>JSON for system integration</li>
                        <li>Excel for detailed analysis</li>
                    </ul>
                    <p>Data exports can include custom date ranges and multiple utilities.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How can I set up consumption alerts?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>In your dashboard settings, you can configure alerts for:</p>
                    <ul>
                        <li>Usage thresholds for each utility</li>
                        <li>Unusual consumption patterns</li>
                        <li>Potential leaks or system anomalies</li>
                        <li>Projected bill amounts</li>
                    </ul>
                    <p>Alerts can be received via email or SMS.</p>
                </div>
            </div>
        </div>

        <div class="faq-section hidden" data-category="technical">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What browsers are supported?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>CLIX web platform works best with:</p>
                    <ul>
                        <li>Google Chrome (version 90+)</li>
                        <li>Mozilla Firefox (version 85+)</li>
                        <li>Microsoft Edge (version 90+)</li>
                        <li>Safari (version 14+)</li>
                    </ul>
                    <p>For optimal performance, please keep your browser updated.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>My smart meter shows "offline" status. What should I do?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Try these troubleshooting steps:</p>
                    <ul>
                        <li>Check if your IoT gateway is powered and its lights are on</li>
                        <li>Ensure your internet connection is active</li>
                        <li>Verify the distance between meter and gateway (should be within 100m)</li>
                        <li>Contact support if problem persists after gateway restart</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How secure is my consumption data?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>We implement multiple security measures:</p>
                    <ul>
                        <li>End-to-end encryption for all data transmission</li>
                        <li>Secure SSL/TLS for web access</li>
                        <li>Regular security audits and penetration testing</li>
                        <li>Data stored in secure Bangladesh data centers</li>
                        <li>Compliance with Digital Security Act 2018</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I integrate CLIX with my home automation system?</h3>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, CLIX offers integration options:</p>
                    <ul>
                        <li>REST API for custom integration</li>
                        <li>Direct integration with major home automation platforms</li>
                        <li>MQTT protocol support for IoT devices</li>
                        <li>Webhook support for automation triggers</li>
                    </ul>
                    <p>Contact our technical team for integration documentation.</p>
                </div>
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
<script src="../js/faq.js"></script>

</body>

</html>
