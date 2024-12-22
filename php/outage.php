<?php
global $conn;
session_start();
require_once './db-connection.php';
?>

<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/outage.css">
    <link rel="stylesheet" href="../css/animation.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './header.php';
?>

<!-- main -->
<?php
try {
    // Pagination
    $items_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;
    $search = $_GET['search'] ?? '';

    if (!empty($search)) {
        $count_query = "SELECT COUNT(*) as total FROM outage_table o WHERE o._affected_area_ LIKE ?";
        $stmt = mysqli_prepare($conn, $count_query);
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "s", $searchParam);
    } else {
        $count_query = "SELECT COUNT(*) as total FROM outage_table o";
        $stmt = mysqli_prepare($conn, $count_query);
    }

    mysqli_stmt_execute($stmt);
    $total_result = mysqli_stmt_get_result($stmt);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_outages = $total_row['total'];
    $total_pages = ceil($total_outages / $items_per_page);

    // Search
    $query = "SELECT o.*, u._utility_id_,
          CASE 
              WHEN ao._active_outage_id_ IS NOT NULL THEN 'Active'
              WHEN ro._resolved_outage_id_ IS NOT NULL THEN 'Resolved'
              ELSE 'Pending'
          END as status
          FROM outage_table o 
          JOIN utility_table u ON o._utility_id_ = u._utility_id_
          LEFT JOIN active_outage_table ao ON o._outage_id_ = ao._active_outage_id_
          LEFT JOIN resolved_outage_table ro ON o._outage_id_ = ro._resolved_outage_id_";

    if (!empty($search)) {
        $query .= " WHERE o._affected_area_ LIKE ?";
        $stmt = mysqli_prepare($conn, $query . " ORDER BY o._start_time_ DESC LIMIT ? OFFSET ?");
        $searchParam = "%$search%";
        mysqli_stmt_bind_param($stmt, "sii", $searchParam, $items_per_page, $offset);
    } else {
        $stmt = mysqli_prepare($conn, $query . " ORDER BY o._start_time_ DESC LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
    }

    mysqli_stmt_execute($stmt);
    $outages = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}
?>

<main id="main-section">
    <h2 id="sub-div-header">Outage Map</h2>
    <div class="mb-3">
        <label for="areaInput"></label><input type="text" id="areaInput" class="form-control"
                                              placeholder="🔍 Search area" autocomplete="off">
        <ul id="suggestions" class="list-group"></ul>
    </div>
    <div id="map"></div>
    <div class="d-flex justify-content-center mt-3">
        <div class="px-3" id="map-gas">■ Gas</div>
        <div class="px-3" id="map-water">■ Water</div>
        <div class="px-3" id="map-electricity">■ Electricity</div>
    </div>
    <div class="mt-3 small d-none">
        <h5>Selected Location</h5>
        <p>Area: <span id="areaName">N/A</span></p>
        <p>Latitude: <span id="latitude">N/A</span></p>
        <p>Longitude: <span id="longitude">N/A</span></p>
    </div>
    <div id="table-section">
        <div>
            <h2 id="sub-div-header">Outage List</h2>
            <div class="py-1">
                <form id="searchForm" method="GET" action="">
                    <input class="form-control" id="client-search" name="search" type="search"
                           placeholder="🔍 Search area" aria-label="Search" style="width: 300px;"
                           value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </form>
            </div>
        </div>
        <table class="table table-borderless">
            <thead>
            <tr>
                <th scope="col" width=1vw>Type</th>
                <th scope="col">Area</th>
                <th scope="col">Outage</th>
                <th scope="col">Start</th>
                <th scope="col">End</th>
                <th scope="col">Status</th>
                <th scope="col">Range</th>
            </tr>
            </thead>
            <tbody id="outageTableBody">
            <?php while ($row = mysqli_fetch_assoc($outages)): ?>
                <tr>
                    <td class="d-flex justify-content-center">
                        <?php
                        $utilityIcon = '';
                        $outageType = '';
                        if (isset($row['_utility_id_'])) {
                            switch ($row['_utility_id_']) {
                                case 2:
                                    $utilityIcon = '../img/gas-costs-svgrepo-com.svg';
                                    $outageType = 'Gas Outage';
                                    break;
                                case 3:
                                    $utilityIcon = '../img/water-fee-svgrepo-com.svg';
                                    $outageType = 'Water Outage';
                                    break;
                                case 1:
                                    $utilityIcon = '../img/hydropower-coal-svgrepo-com.svg';
                                    $outageType = 'Electricity Outage';
                                    break;
                            }
                        }
                        ?>
                        <img class="utility-svg" src="<?php echo $utilityIcon; ?>" alt="<?php echo $outageType; ?>">
                    </td>
                    <td><?php echo htmlspecialchars($row['_affected_area_']); ?></td>
                    <td><?php echo $outageType; ?></td>
                    <td><?php echo date('h:ia (d-M-Y)', strtotime($row['_start_time_'])); ?></td>
                    <td><?php echo date('h:ia (d-M-Y)', strtotime($row['_end_time_'])); ?></td>
                    <td>
                        <?php
                        $statusClass = '';
                        $statusIcon = '';

                        switch ($row['status']) {
                            case 'Active':
                                $statusClass = 'text-bg-warning';
                                $statusIcon = 'bi bi-clock';
                                break;
                            case 'Resolved':
                                $statusClass = 'text-bg-success';
                                $statusIcon = 'bi bi-check-circle';
                                break;
                            case 'Closed':
                                $statusClass = 'text-bg-secondary';
                                $statusIcon = 'bi bi-x-circle';
                                break;
                            default:
                                $statusClass = 'text-bg-secondary';
                                $statusIcon = 'bi bi-dash-circle';
                        }
                        ?>
                        <span class="badge rounded-pill <?php echo $statusClass; ?>">
                            <i class="<?php echo $statusIcon; ?> me-1"></i>
                            <?php echo htmlspecialchars($row['status'] ?? 'Unknown'); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($row['_range_km_'], 1); ?>km</td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <!-- pagination -->
        <?php
        require_once './pagination.php';
        ?>

    </div>
</main>

<!-- footer -->
<?php
require_once './footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/leaflet.js"></script>
<script src="../js/outage.js"></script>

</body>

</html>
