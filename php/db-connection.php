<?php
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "clixdb";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
