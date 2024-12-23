<?php
global $conn;
if (!isset($_SESSION['_user_id_'])) {
    header("Location: access-denied.php");
    exit;
}
?>

<footer class="border-top border-bottom" id="footer-section">
    <div class="text-center">
        <p class="mb-0">© 2024 CLIX. All Rights Reserved.</p>
    </div>
</footer>
