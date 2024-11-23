<?php
include('db-connection.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $area = mysqli_real_escape_string($conn, $_POST['areaName']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $range = mysqli_real_escape_string($conn, $_POST['radiusInput']);
    $type = mysqli_real_escape_string($conn, $_POST['notificationType']);
    $time_start = mysqli_real_escape_string($conn, $_POST['startTime']);
    $date_start = mysqli_real_escape_string($conn, $_POST['startDate']);
    $time_end = mysqli_real_escape_string($conn, $_POST['endTime']);
    $date_end = mysqli_real_escape_string($conn, $_POST['endDate']);

    if (empty($area) || empty($latitude) || empty($longitude) || empty($range) || empty($type) || empty($time_start) || empty($date_start) || empty($time_end) || empty($date_end)) {
        die("Please fill in all required fields.");
    }

    $query = "INSERT INTO outage_t (_time_start_, _time_end_, _date_start_, _date_end_, _area_, _latitude_, _longitude_, _range_, _type_)
              VALUES ('$time_start', '$time_end', '$date_start', '$date_end', '$area', '$latitude', '$longitude', '$range', '$type')";

    if (mysqli_query($conn, $query)) {
        echo "Outage added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
