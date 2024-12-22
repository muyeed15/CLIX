<?php
session_start();
require_once './php/db-connection.php';

$isLoggedIn = isset($_SESSION['_user_id_']);

if ($isLoggedIn) {
    $user_id = $_SESSION['_user_id_'];

    try {
        global $conn;

        $clientCheckQuery = "SELECT c._client_id_ 
                            FROM client_table c 
                            WHERE c._client_id_ = ?";

        $adminCheckQuery = "SELECT a._admin_id_ 
                           FROM admin_table a 
                           WHERE a._admin_id_ = ?";

        $stmt = mysqli_prepare($conn, $clientCheckQuery);
        if (!$stmt) {
            error_log("Failed to prepare client query: " . mysqli_error($conn));
            header("Location: ./php/access-denied.php");
            exit;
        }

        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $clientResult = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_num_rows($clientResult) > 0) {
            header("Location: ./php/home.php");
            exit;
        } else {
            $stmt = mysqli_prepare($conn, $adminCheckQuery);
            if (!$stmt) {
                error_log("Failed to prepare admin query: " . mysqli_error($conn));
                header("Location: ./php/access-denied.php");
                exit;
            }

            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $adminResult = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);

            if (mysqli_num_rows($adminResult) > 0) {
                header("Location: ./php/admin-dashboard.php");
                exit;
            } else {
                session_destroy();
                header("Location: ./php/access-denied.php");
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Login check error: " . $e->getMessage());
        session_destroy();
        header("Location: ./php/access-denied.php");
        exit;
    }
} else {
    header("Location: ./php/home.php");
    exit;
}
