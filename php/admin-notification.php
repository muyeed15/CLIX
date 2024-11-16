<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Convenient Living & Integrated Experience</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/leaflet.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin-outage.css">
</head>

<body>
    <!-- Header -->
    <header class="border-bottom" id="header-section">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <a href="#">
                    <img class="py-2" src="../img/CLIX.svg" id="header-logo">
                </a>

                <ul class="nav small py-2">
                    <li><a href="./admin-dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                    <li><a href="./admin-outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    <li><a href="./admin-IoT-control.php" class="nav-link px-3 link-body-emphasis">IoT</a></li>
                    <li><a href="./admin-notification.php" class="nav-link px-3 link-secondary">Notification</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">Payment</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">Seasion</a></li>
                    <li><a href="./" class="nav-link px-3 link-body-emphasis">User</a></li>
                </ul>

                <div class="d-flex py-2">
                    <div class="dropdown text-end" id="notification-icon">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                class="bi bi-bell" viewBox="0 0 16 16">
                                <path
                                    d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item small" href="#">Your electricity bill is due tomorrow.</a></li>
                            <li><a class="dropdown-item small" href="#">Gas service will be disrupted from 10:00 AM to 1:00 PM.</a></li>
                            <li><a class="dropdown-item small" href="#">Your water usage is higher than usual today.</a></li>
                            <li><a class="dropdown-item small" href="#">Check your gas connections for leaks.</a></li>
                            <li><a class="dropdown-item small" href="#">There may be low water pressure today.</a></li>
                            <li><a class="dropdown-item small" href="#">Reduce usage during peak hours.</a></li>
                            <li><a class="dropdown-item small" href="#">Power outage expected from 3:00 PM to 5:00 PM.</a></li>
                            <li><a class="dropdown-item small" href="#">Your gas bill is due in 3 days.</a></li>
                            <li><a class="dropdown-item small" href="#">Water supply will be disrupted tomorrow.</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../img/LinkedIn_1x1_1000px.jpg" alt="mdo" width="32" height="32" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="#">Profile</a></li>
                            <li><a class="dropdown-item small" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="#">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Section -->
    <main id="main-section">
        <h2 id="sub-div-header">Notification</h2>
        <div class="card mb-4" id="create-notification-card">
            <div class="card-body">
                <h5 class="card-title">Create Notification</h5>
                <form id="create-notification-form">
                    <div class="mb-3">
                        <label for="notification-type" class="form-label">Type</label>
                        <select class="form-select" id="notification-type">
                            <option value="Electricity">Electricity</option>
                            <option value="Gas">Gas</option>
                            <option value="Water">Water</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notification-header" class="form-label">Header</label>
                        <input type="text" class="form-control" id="notification-header" placeholder="Enter header">
                    </div>
                    <div class="mb-3">
                        <label for="notification-message" class="form-label">Message</label>
                        <textarea class="form-control" id="notification-message" rows="3" placeholder="Enter message"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="notification-date-time" class="form-label">Date and Time</label>
                        <input type="datetime-local" class="form-control" id="notification-date-time">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Notification</button>
                </form>
            </div>
        </div>

        <div class="mb-3">
            <label for="search-notifications" class="form-label">Search Notifications</label>
            <input type="text" class="form-control" id="search-notifications" placeholder="🔍 Search notification">
        </div>
        
        <div class="card mb-4" id="create-notification-card">
            <div class="card-body">
                <table class="table table-borderless small" id="notifications-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Header</th>
                            <th>Message</th>
                            <th>Time</th>
                            <th>Date</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody id="notifications-table-body">
                        <tr>
                            <td>1</td>
                            <td>Electricity</td>
                            <td>Power Outage</td>
                            <td>Power outage expected from 3:00 PM to 5:00 PM.</td>
                            <td>15:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Water</td>
                            <td>Low Water Pressure</td>
                            <td>There may be low water pressure today.</td>
                            <td>10:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Gas</td>
                            <td>Gas Leak Warning</td>
                            <td>Check your gas connections for leaks.</td>
                            <td>08:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Electricity</td>
                            <td>Scheduled Maintenance</td>
                            <td>Electricity maintenance from 2:00 PM to 4:00 PM.</td>
                            <td>14:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Water</td>
                            <td>Water Supply Disruption</td>
                            <td>Water supply will be disrupted from 6:00 AM to 9:00 AM.</td>
                            <td>06:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Gas</td>
                            <td>Gas Service Restoration</td>
                            <td>Gas services will be restored by 7:00 PM today.</td>
                            <td>19:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Electricity</td>
                            <td>Power Outage</td>
                            <td>Expected power outage from 3:30 PM to 5:30 PM.</td>
                            <td>15:30</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Water</td>
                            <td>Water Pressure Issues</td>
                            <td>Water pressure may be lower than usual in some areas.</td>
                            <td>09:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Gas</td>
                            <td>Gas Supply Test</td>
                            <td>Gas supply will be tested between 11:00 AM and 1:00 PM.</td>
                            <td>11:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Electricity</td>
                            <td>Planned Power Cut</td>
                            <td>Planned power cut from 12:00 PM to 2:00 PM.</td>
                            <td>12:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>Water</td>
                            <td>Water Maintenance</td>
                            <td>Water maintenance from 8:00 AM to 10:00 AM.</td>
                            <td>08:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>Gas</td>
                            <td>Gas Maintenance</td>
                            <td>Gas maintenance work scheduled from 7:00 AM to 9:00 AM.</td>
                            <td>07:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td>Electricity</td>
                            <td>Unscheduled Power Outage</td>
                            <td>Unexpected power outage from 4:30 PM to 6:30 PM.</td>
                            <td>16:30</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>Water</td>
                            <td>Low Water Supply</td>
                            <td>Expect low water supply in certain areas today.</td>
                            <td>13:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>Gas</td>
                            <td>Gas Disruption</td>
                            <td>Gas supply will be disrupted from 2:00 PM to 4:00 PM.</td>
                            <td>14:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>Electricity</td>
                            <td>Power Maintenance</td>
                            <td>Power maintenance from 5:00 AM to 7:00 AM.</td>
                            <td>05:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>Water</td>
                            <td>Water Supply Expected Delay</td>
                            <td>Water supply may be delayed by up to 2 hours today.</td>
                            <td>07:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>Gas</td>
                            <td>Gas Supply Issue</td>
                            <td>Gas supply pressure may fluctuate today.</td>
                            <td>09:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>19</td>
                            <td>Electricity</td>
                            <td>Temporary Power Outage</td>
                            <td>Temporary outage expected in the next 30 minutes.</td>
                            <td>16:30</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>Water</td>
                            <td>Water Supply Restoration</td>
                            <td>Water supply will be restored by 11:00 AM.</td>
                            <td>11:00</td>
                            <td>2024-11-16</td>
                            <td class="d-flex"><img id="edit-svg" src="../img/edit-svgrepo-com.svg"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center" id="pagination-section">
                <nav aria-label="Page navigation example">
                    <ul class="pagination no-border">
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img src="../img/CLIX.svg" width="46">
                <small class="d-block mb-3 text-body-secondary">©2024</small>
                <p class="small text-body-secondary">
                    Why CLIX?<br>
                    Convenient Living<br>
                    Integrated Experience
                </p>
            </div>
            <div class="col-3">
                <h5>Links</h5>
                <ul class=" list-unstyled">
                    <li><a class="link-secondary text-decoration-none small" href="#">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="#">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="#">Phone: +8801712345678</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/leaflet.js"></script>
    <script src="../js/admin-outage.js"></script>
</body>

</html>