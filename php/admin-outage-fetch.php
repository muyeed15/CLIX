<?php
include 'db-connection.php';

header('Content-Type: application/json');

$sql = "SELECT _outage_id_, _time_start_, _time_end_, _date_start_, _date_end_, _area_, _latitude_, _longitude_, _range_, _type_ FROM outage_t";
$result = mysqli_query($conn, $sql);

$outages = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $outages[] = $row;
    }
}

echo json_encode($outages);
?>
