<?php
global $conn;
session_start();
require_once './php/db-connection.php';

$isLoggedIn = isset($_SESSION['_user_id_']);

if ($isLoggedIn) {
    $user_id = $_SESSION['_user_id_'];

    $clientCheckQuery = "SELECT c._client_id_ 
                        FROM client_table c 
                        WHERE c._client_id_ = ?";

    $adminCheckQuery = "SELECT a._admin_id_ 
                       FROM admin_table a 
                       WHERE a._admin_id_ = ?";

    $stmt = mysqli_prepare($conn, $clientCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $clientResult = mysqli_stmt_get_result($stmt);

    $stmt = mysqli_prepare($conn, $adminCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $adminResult = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($clientResult) > 0) {
        header("Location: ./php/home.php");
    } elseif (mysqli_num_rows($adminResult) > 0) {
        header("Location: ./php/admin-dashboard.php");
    } else {
        header("Location: ./php/access-denied.php");
    }
    exit;
}
