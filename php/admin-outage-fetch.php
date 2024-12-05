<?php
include 'db-connection.php';

header('Content-Type: application/json');

$sql = "SELECT 
    o._outage_id_,
    TIME(o._start_time_) as _time_start_,
    TIME(o._end_time_) as _time_end_,
    DATE(o._start_time_) as _date_start_,
    DATE(o._end_time_) as _date_end_,
    o._affected_area_ as _area_,
    o._latitude_,
    o._longitude_,
    (o._range_km_ * 1000) as _range_,
    CASE 
        WHEN w._water_id_ IS NOT NULL THEN 'Water'
        WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
        WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
        ELSE 'UNKNOWN'
    END as _type_
FROM outage_table o
LEFT JOIN utility_table u ON o._utility_id_ = u._utility_id_
LEFT JOIN water_table w ON u._utility_id_ = w._water_id_
LEFT JOIN gas_table g ON u._utility_id_ = g._gas_id_
LEFT JOIN electricity_table e ON u._utility_id_ = e._electricity_id_;";
$result = mysqli_query($conn, $sql);

$outages = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $outages[] = $row;
    }
}

echo json_encode($outages);
?>
