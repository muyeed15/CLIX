<?php
session_start();
require_once './db-connection.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
        $password = $_POST['password'] ?? '';
        $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT) ?? 23.8103;
        $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT) ?? 90.4125;

        if (empty($email) || empty($password)) {
            throw new Exception("All fields must be filled out");
        }

        $query = "SELECT * FROM user_table WHERE _email_ = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $client_check = $conn->prepare("SELECT * FROM client_table WHERE _client_id_ = ?");
            $client_check->bind_param("i", $user['_user_id_']);
            $client_check->execute();
            $client_result = $client_check->get_result();

            if ($client_result->num_rows === 0) {
                throw new Exception("Invalid email or password");
            }

            if (hash('sha256', $password) === $user['_password_']) {
                $_SESSION['_user_id_'] = $user['_user_id_'];

                $ip_address = $_SERVER['REMOTE_ADDR'];
                $device_name = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
                
                // logs
                $log_stmt = $conn->prepare("INSERT INTO user_login_log_table (
                    _user_id_, 
                    _log_time_, 
                    _ip_address_, 
                    _device_latitude_, 
                    _device_longitude_, 
                    _device_name_
                ) VALUES (?, NOW(), ?, ?, ?, ?)");
                
                $log_stmt->bind_param("isdds", 
                    $user['_user_id_'], 
                    $ip_address,
                    $latitude,
                    $longitude,
                    $device_name
                );
                $log_stmt->execute();

                header("Location: ../index.php");
                exit();
            }
        }
        
        throw new Exception("Invalid email or password");

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Login</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/animation.css">
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/10996977-hd_1920_1080_60fps.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div id="header-logo-container">
        <a href="../index.php">
            <img class="py-2" src="../img/CLIX.svg" id="header-logo" alt="Logo">
        </a>
    </div>

    <main class="form-signin w-100 m-auto">
        <form method="POST" action="" class="needs-validation" novalidate id="loginForm">
            <h1 class="h3 mt-4 mb-4 fw-normal text-center" id="sign-h1">Sign In</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-floating">
                <input name="email" type="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                <label for="floatingInput" id="floatingInputText">Email</label>
            </div>
            <div class="form-floating mt-3">
                <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword" id="floatingPasswordText">Password</label>
            </div>

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            
            <div class="my-3 d-flex justify-content-between align-items-center">
                <a href="forgot-password.php" class="sign-up">Forgot Password?</a>
                <a href="./signup.php" class="sign-up">New Here? Sign Up</a>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3 mb-4">Sign in</button>
        </form>
    </main>

    <script>
        function getLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                }, function(error) {
                    document.getElementById('latitude').value = 23.8103;
                    document.getElementById('longitude').value = 90.4125;
                });
            } else {
                document.getElementById('latitude').value = 23.8103;
                document.getElementById('longitude').value = 90.4125;
            }
        }

        getLocation();

        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
        })();
    </script>
</body>
</html>