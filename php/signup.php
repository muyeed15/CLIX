<?php
session_start();
require_once './db-connection.php';
$error = '';

// signup
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $nid = !empty($_POST['nid']) ? filter_input(INPUT_POST, 'nid', FILTER_SANITIZE_NUMBER_INT) : null;
        $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $current_address = htmlspecialchars(trim($_POST['current_address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($date_of_birth) || 
            empty($email) || empty($phone) || empty($current_address) || empty($password)) {
            throw new Exception("All required fields must be filled out");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (!preg_match("/^[0-9+\-\s()]*$/", $phone)) {
            throw new Exception("Invalid phone number format");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
            throw new Exception("Password must contain at least one uppercase letter, one lowercase letter, and one number");
        }

        $birthDate = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        if ($age < 18) {
            throw new Exception("You must be at least 18 years old to register");
        }

        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            if ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Profile picture must be less than 5MB");
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['profile_picture']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed");
            }

            $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
        }

        $hashed_password = hash('sha256', $password);

        $conn->begin_transaction();

        $check_email = $conn->prepare("SELECT _email_ FROM user_table WHERE _email_ = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            throw new Exception("This email is already registered");
        }

        $check_phone = $conn->prepare("SELECT _phone_ FROM user_table WHERE _phone_ = ?");
        $check_phone->bind_param("s", $phone);
        $check_phone->execute();
        if ($check_phone->get_result()->num_rows > 0) {
            throw new Exception("This phone number is already registered");
        }

        if ($nid !== null) {
            $check_nid = $conn->prepare("SELECT _nid_ FROM user_table WHERE _nid_ = ?");
            $check_nid->bind_param("i", $nid);
            $check_nid->execute();
            if ($check_nid->get_result()->num_rows > 0) {
                throw new Exception("This NID is already registered");
            }
        }

        $stmt = $conn->prepare("INSERT INTO user_table (_first_name_, _last_name_, _date_of_birth_, _nid_, 
                               _email_, _phone_, _current_address_, _password_, _profile_picture_) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssssss", $first_name, $last_name, $date_of_birth, $nid, 
                         $email, $phone, $current_address, $hashed_password, $profile_picture);

        if (!$stmt->execute()) {
            throw new Exception("Error creating user account: " . $stmt->error);
        }

        $user_id = $conn->insert_id;

        $client_stmt = $conn->prepare("INSERT INTO client_table (_client_id_) VALUES (?)");
        $client_stmt->bind_param("i", $user_id);
        
        if (!$client_stmt->execute()) {
            throw new Exception("Error creating client record: " . $client_stmt->error);
        }

        $notification_stmt = $conn->prepare("INSERT INTO notification_table (_user_id_, _notification_time_, 
                                           _notification_title_, _notification_message_) 
                                           VALUES (?, NOW(), ?, ?)");
        
        $welcome_title = "Welcome to CLIX!";
        $welcome_message = "Welcome to CLIX, {$first_name}! Thank you for joining our platform.";
        $notification_stmt->bind_param("iss", $user_id, $welcome_title, $welcome_message);
        $notification_stmt->execute();

        $ip_address = $_SERVER['REMOTE_ADDR'];
        $log_stmt = $conn->prepare("INSERT INTO user_login_log_table (_user_id_, _log_time_, _ip_address_, 
                                   _device_latitude_, _device_longitude_, _device_name_) 
                                   VALUES (?, NOW(), ?, 0, 0, ?)"); // Default coordinates if not available
        
        $device_name = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
        $log_stmt->bind_param("iss", $user_id, $ip_address, $device_name);
        $log_stmt->execute();

        $conn->commit();

        $_SESSION['success_message'] = "Account created successfully! Please log in.";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        if (isset($conn) && $conn->connect_error === false) {
            $conn->rollback();
        }

        $error = $e->getMessage();
        $_SESSION['error_message'] = $error;
    }
}

if (!empty($error)) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Sign Up</title>

    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/animation.css">
    <link rel="stylesheet" href="../css/signup.css">

</head>

<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/10996977-hd_1920_1080_60fps.mp4" type="video/mp4">
    </video>

    <div id="header-logo-container">
        <a href="../index.php">
            <img class="py-2" src="../img/CLIX.svg" id="header-logo" alt="Logo">
        </a>
    </div>

    <main class="form-signin">
        <div class="form-content">
            <form method="POST" action="" class="needs-validation" enctype="multipart/form-data" novalidate>
                <h1 id="sign-h1">Create Account</h1>

                <div class="profile-upload-container mb-3">
                    <img id="profilePreview" class="profile-preview" src="../img/user-rounded-svgrepo-com.jpg" alt="Profile Preview">
                    <label for="profilePicture" class="profile-upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </label>
                    <input type="file" id="profilePicture" name="profile_picture" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-floating">
                        <input name="first_name" type="text" class="form-control" id="floatingFirstName" placeholder="John" required>
                        <label for="floatingFirstName">First Name</label>
                    </div>
                    <div class="form-floating">
                        <input name="last_name" type="text" class="form-control" id="floatingLastName" placeholder="Doe" required>
                        <label for="floatingLastName">Last Name</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-floating">
                        <input name="date_of_birth" type="date" class="form-control" id="floatingDOB" required>
                        <label for="floatingDOB">Date of Birth</label>
                    </div>
                    <div class="form-floating">
                        <input name="nid" type="number" class="form-control" id="floatingNID" placeholder="NID Number">
                        <label for="floatingNID">NID (Optional)</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-floating">
                        <input name="email" type="email" class="form-control" id="floatingEmail" placeholder="name@example.com" required>
                        <label for="floatingEmail">Email</label>
                    </div>
                    <div class="form-floating">
                        <input name="phone" type="tel" class="form-control" id="floatingPhone" placeholder="Phone Number" required>
                        <label for="floatingPhone">Phone</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-floating">
                        <input name="current_address" type="text" class="form-control" id="floatingAddress" placeholder="Address" required>
                        <label for="floatingAddress">Address</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-floating">
                        <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                        <label for="floatingPassword">Password</label>
                    </div>
                    <div class="form-floating">
                        <input name="confirm_password" type="password" class="form-control" id="floatingConfirmPassword" placeholder="Confirm Password" required>
                        <label for="floatingConfirmPassword">Confirm Password</label>
                    </div>
                </div>

                <button class="w-100 btn btn-lg btn-primary mt-1" type="submit">Create Account</button>
                <div class="text-center mt-3 mb-2">
                    <a href="./login.php" class="sign-up">Already have an account? Sign In</a>
                </div>
            </form>
        </div>
    </main>
    
    <!-- script -->
    <script src="../js/signup.js"></script>

</body>
</html>