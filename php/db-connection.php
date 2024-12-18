<?php
$servername = "118.139.179.92:3306";
$username = "clix_user";
$password = "F9sqtmJx9kqj9FP";
$dbname = "clix_database";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
