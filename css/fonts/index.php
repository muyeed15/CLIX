<?php
global $conn;
if (!isset($_SESSION['_user_id_'])) {
    header("Location: ../../php/access-denied.php");
    exit;
}
