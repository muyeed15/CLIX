<?php
$servername = "localhost:3306";
$username = "root";
$password = "12345678";
$dbname = "clix_database";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

