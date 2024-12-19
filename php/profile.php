<?php
session_start();
require_once './db-connection.php';

if (!isset($_SESSION['_user_id_'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['_user_id_'];

$clientCheckQuery = "SELECT c._client_id_ 
                    FROM client_table c 
                    WHERE c._client_id_ = ?";

try {
    $stmt = mysqli_prepare($conn, $clientCheckQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        header("Location: access-denied.php");
        exit;
    }

    // Notification
    $notificationQuery = "SELECT * FROM notification_table
                        WHERE _user_id_ = ? OR _user_id_ IS NULL
                        ORDER BY _notification_time_ DESC
                        LIMIT 10";

    $stmt = mysqli_prepare($conn, $notificationQuery);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $notifications = mysqli_stmt_get_result($stmt);

    // User Picture
    $pictureQuery = "SELECT _profile_picture_ FROM user_table
                    WHERE _user_id_ = ?";

    $stmt = mysqli_prepare($conn, $pictureQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $picture = mysqli_stmt_get_result($stmt);

    if (($row = mysqli_fetch_assoc($picture)) && (!empty($row['_profile_picture_']) && $row['_profile_picture_'] !== NULL)) {
        $pictureData = $row['_profile_picture_'];
        $base64Image = base64_encode($pictureData);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
    }

    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
}

$success_message = '';
$error_message = '';
$password_success = '';
$password_error = '';

// Handle password update
if (isset($_POST['update_password'])) {
    try {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            throw new Exception("All password fields are required");
        }

        // Verify current password
        $check_password = $conn->prepare("SELECT _password_ FROM user_table WHERE _user_id_ = ?");
        $check_password->bind_param("i", $user_id);
        $check_password->execute();
        $result = $check_password->get_result();
        $current_hash = $result->fetch_assoc()['_password_'];

        if (hash('sha256', $current_password) !== $current_hash) {
            throw new Exception("Current password is incorrect");
        }

        if ($new_password !== $confirm_new_password) {
            throw new Exception("New passwords do not match");
        }

        if (strlen($new_password) < 8) {
            throw new Exception("New password must be at least 8 characters long");
        }

        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $new_password)) {
            throw new Exception("New password must contain at least one uppercase letter, one lowercase letter, and one number");
        }

        $new_hash = hash('sha256', $new_password);
        
        $update_password = $conn->prepare("UPDATE user_table SET _password_ = ? WHERE _user_id_ = ?");
        $update_password->bind_param("si", $new_hash, $user_id);
        
        if (!$update_password->execute()) {
            throw new Exception("Error updating password");
        }

        $password_success = "Password updated successfully!";

    } catch (Exception $e) {
        $password_error = $e->getMessage();
    }
}

// Fetch current user data
try {
    $stmt = $conn->prepare("SELECT * FROM user_table WHERE _user_id_ = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    // Get profile picture
    if (!empty($user_data['_profile_picture_'])) {
        $base64Image = base64_encode($user_data['_profile_picture_']);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn->begin_transaction();

        try {
            // Validate and sanitize input
            $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $date_of_birth = trim($_POST['date_of_birth'] ?? '');
            $nid = !empty($_POST['nid']) ? filter_input(INPUT_POST, 'nid', FILTER_SANITIZE_NUMBER_INT) : null;
            $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
            $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
            $current_address = htmlspecialchars(trim($_POST['current_address'] ?? ''), ENT_QUOTES, 'UTF-8');
            
            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($date_of_birth) || 
                empty($email) || empty($phone) || empty($current_address)) {
                throw new Exception("All required fields must be filled out");
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Validate phone format
            if (!preg_match("/^[0-9+\-\s()]*$/", $phone)) {
                throw new Exception("Invalid phone number format");
            }

            // Check age
            $birthDate = new DateTime($date_of_birth);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            if ($age < 18) {
                throw new Exception("You must be at least 18 years old");
            }

            // Handle profile picture upload
            $profile_picture_sql = '';
            $profile_picture_params = '';
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
                $profile_picture_sql = ', _profile_picture_ = ?';
                $profile_picture_params = 's';
            }

            // Check if email is already used by another user
            if ($email !== $user_data['_email_']) {
                $check_email = $conn->prepare("SELECT _email_ FROM user_table WHERE _email_ = ? AND _user_id_ != ?");
                $check_email->bind_param("si", $email, $user_id);
                $check_email->execute();
                if ($check_email->get_result()->num_rows > 0) {
                    throw new Exception("This email is already registered");
                }
            }

            // Check if phone is already used by another user
            if ($phone !== $user_data['_phone_']) {
                $check_phone = $conn->prepare("SELECT _phone_ FROM user_table WHERE _phone_ = ? AND _user_id_ != ?");
                $check_phone->bind_param("si", $phone, $user_id);
                $check_phone->execute();
                if ($check_phone->get_result()->num_rows > 0) {
                    throw new Exception("This phone number is already registered");
                }
            }

            // Check if NID is already used by another user
            if ($nid !== null && $nid !== $user_data['_nid_']) {
                $check_nid = $conn->prepare("SELECT _nid_ FROM user_table WHERE _nid_ = ? AND _user_id_ != ?");
                $check_nid->bind_param("ii", $nid, $user_id);
                $check_nid->execute();
                if ($check_nid->get_result()->num_rows > 0) {
                    throw new Exception("This NID is already registered");
                }
            }

            // Update user information
            $sql = "UPDATE user_table SET 
                    _first_name_ = ?, 
                    _last_name_ = ?, 
                    _date_of_birth_ = ?, 
                    _nid_ = ?, 
                    _email_ = ?, 
                    _phone_ = ?, 
                    _current_address_ = ?" . $profile_picture_sql . "
                    WHERE _user_id_ = ?";

            $stmt = $conn->prepare($sql);
            
            $types = "sssssss" . $profile_picture_params . "i";
            $params = [$first_name, $last_name, $date_of_birth, $nid, $email, $phone, $current_address];
            if (!empty($profile_picture_params)) {
                $params[] = $profile_picture;
            }
            $params[] = $user_id;
            
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Error updating profile: " . $stmt->error);
            }

            $conn->commit();
            $success_message = "Profile updated successfully!";
            
            // Refresh user data after update
            $stmt = $conn->prepare("SELECT * FROM user_table WHERE _user_id_ = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            if (!empty($user_data['_profile_picture_'])) {
                $base64Image = base64_encode($user_data['_profile_picture_']);
                $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
            }

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }

} catch (Exception $e) {
    $error_message = "Error fetching user data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Profile</title>

    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/animation.css">
    <style>
        .profile-section {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 2rem;
        }
        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-picture-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #007bff;
            border-radius: 50%;
            padding: 8px;
            cursor: pointer;
        }
        .profile-picture-upload svg {
            width: 20px;
            height: 20px;
            color: white;
        }
        #profilePicture {
            display: none;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .alert {
            margin-bottom: 1rem;
        }
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- header -->
    <header class="border-bottom" id="header-section">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../index.php" class="d-flex align-items-center mb-lg-0">
                    <img src="../img/CLIX.svg" id="header-logo" alt="Logo" class="img-fluid">
                </a>
                
                <!-- Navbar -->
                <nav class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <ul class="nav">
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    </ul>
                </nav>

                <!-- Notification, Mobile Navbar and User Section -->
                <div class="d-flex align-items-center">
                    <!-- Mobile Navbar Toggle -->
                    <button class="navbar-toggler d-lg-none" type="button" style="width: 50px; height: 50px;" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </span>
                    </button>

                    <!-- Notifications -->
                    <div class="dropdown text-end me-2" id="notification-icon">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17px" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <?php while ($row = mysqli_fetch_assoc($notifications)) : ?>
                                <li><a class="dropdown-item small" href="#"><?= htmlspecialchars($row['_notification_message_']); ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- User Picture -->
                    <div class="dropdown text-end" id="user-picture">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="<?php echo $imageSrc; ?>" alt="User" class="rounded-circle" style="width: 36px; height: 36px;">
                        </a>
                        <ul class="dropdown-menu text-small">
                            <li><a class="dropdown-item small" href="./profile.php">Profile</a></li>
                            <li><a class="dropdown-item small" href="./settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="./logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Collapsible Mobile Menu -->
            <div class="collapse" id="mobileNav">
                <nav class="navbar-nav">
                    <ul class="nav flex-column text-center">
                        <li><a href="../" class="nav-link px-3 link-body-emphasis">Home</a></li>
                        <li><a href="./dashboard.php" class="nav-link px-3 link-body-emphasis">Dashboard</a></li>
                        <li><a href="./history.php" class="nav-link px-3 link-body-emphasis">History</a></li>
                        <li><a href="./outage.php" class="nav-link px-3 link-body-emphasis">Outage</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="profile-section">
                <h2 class="text-center mb-4">Profile Settings</h2>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="profile-picture-container">
                        <img id="profilePreview" class="profile-picture" src="<?php echo $imageSrc; ?>" alt="Profile Picture">
                        <label for="profilePicture" class="profile-picture-upload">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </label>
                        <input type="file" id="profilePicture" name="profile_picture" accept="image/*">
                    </div>

                    <div class="form-row">
                        <div class="form-floating">
                            <input name="first_name" type="text" class="form-control" id="floatingFirstName" 
                                   placeholder="First Name" value="<?php echo htmlspecialchars($user_data['_first_name_']); ?>" required>
                            <label for="floatingFirstName">First Name</label>
                        </div>
                        <div class="form-floating">
                            <input name="last_name" type="text" class="form-control" id="floatingLastName" 
                                   placeholder="Last Name" value="<?php echo htmlspecialchars($user_data['_last_name_']); ?>" required>
                            <label for="floatingLastName">Last Name</label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-floating">
                            <input name="date_of_birth" type="date" class="form-control" id="floatingDOB" 
                                   value="<?php echo htmlspecialchars($user_data['_date_of_birth_']); ?>" required>
                            <label for="floatingDOB">Date of Birth</label>
                        </div>
                        <div class="form-floating">
                            <input name="nid" type="number" class="form-control" id="floatingNID" 
                                   placeholder="NID Number" value="<?php echo htmlspecialchars($user_data['_nid_'] ?? ''); ?>">
                            <label for="floatingNID">NID (Optional)</label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-floating">
                            <input name="email" type="email" class="form-control" id="floatingEmail" 
                                   placeholder="name@example.com" value="<?php echo htmlspecialchars($user_data['_email_']); ?>" required>
                            <label for="floatingEmail">Email</label>
                        </div>
                        <div class="form-floating">
                            <input name="phone" type="tel" class="form-control" id="floatingPhone" 
                                   placeholder="Phone Number" value="<?php echo htmlspecialchars($user_data['_phone_']); ?>" required>
                            <label for="floatingPhone">Phone</label>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input name="current_address" type="text" class="form-control" id="floatingAddress" 
                               placeholder="Address" value="<?php echo htmlspecialchars($user_data['_current_address_']); ?>" required>
                        <label for="floatingAddress">Address</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>

                <hr class="my-4">

                <!-- Password Update Section -->
                <h3 class="text-center mb-4">Change Password</h3>

                <?php if (!empty($password_success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($password_success); ?></div>
                <?php endif; ?>

                <?php if (!empty($password_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($password_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input name="current_password" type="password" class="form-control" id="floatingCurrentPassword" 
                               placeholder="Current Password" required>
                        <label for="floatingCurrentPassword">Current Password</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input name="new_password" type="password" class="form-control" id="floatingNewPassword" 
                               placeholder="New Password" required>
                        <label for="floatingNewPassword">New Password</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input name="confirm_new_password" type="password" class="form-control" id="floatingConfirmNewPassword" 
                               placeholder="Confirm New Password" required>
                        <label for="floatingConfirmNewPassword">Confirm New Password</label>
                    </div>

                    <button type="submit" name="update_password" class="btn btn-warning w-100">Update Password</button>
                </form>
            </div>
        </div>
    </main>
    <!-- footer -->
    <footer class="border-top border-bottom" id="footer-section">
        <div class="row justify-content-between py-2">
            <div class="col-3">
                <img class="footer-logo" src="../img/CLIX.svg">
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
                    <li><a class="link-secondary text-decoration-none small" href="./about.php">About Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./contact.php">Contact Us</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./privacy.php">Privacy Policy</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./term.php">Terms & Conditions</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="./faq.php">FAQ & Help</a></li>
                </ul>
            </div>
            <div class="col-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none small" href="">Address: Dhaka, Bangladesh</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="mailto:clix@mail.com">Email: clix@mail.com</a></li>
                    <li><a class="link-secondary text-decoration-none small" href="">Phone: +8801712345678</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- script -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script>
    // Profile Form Validation
    document.addEventListener('DOMContentLoaded', function() {
        // Form elements
        const profileForm = document.querySelector('form');
        const profilePictureInput = document.getElementById('profilePicture');
        const profilePreview = document.getElementById('profilePreview');
        const newPasswordInput = document.getElementById('floatingNewPassword');
        const confirmPasswordInput = document.getElementById('floatingConfirmNewPassword');
        const phoneInput = document.getElementById('floatingPhone');
        const dobInput = document.getElementById('floatingDOB');
        const nidInput = document.getElementById('floatingNID');

        // Profile Picture Preview
        if (profilePictureInput) {
            profilePictureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        this.value = '';
                        return;
                    }

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert('Only JPG, PNG, and GIF files are allowed');
                        this.value = '';
                        return;
                    }

                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Password Requirements Tooltip
        if (newPasswordInput) {
            const requirementsDiv = document.createElement('div');
            requirementsDiv.className = 'password-requirements';
            newPasswordInput.parentNode.insertBefore(requirementsDiv, newPasswordInput.nextSibling);

            // Password validation
            function validatePassword(password) {
                const minLength = password.length >= 8;
                const hasUpper = /[A-Z]/.test(password);
                const hasLower = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                
                return minLength && hasUpper && hasLower && hasNumber;
            }

            newPasswordInput.addEventListener('input', function() {
                const isValid = validatePassword(this.value);
                this.setCustomValidity(isValid ? '' : 'Password does not meet requirements');
                requirementsDiv.style.color = isValid ? '#28a745' : '#6c757d';
            });

            // Password matching validation
            if (confirmPasswordInput) {
                function validatePasswordMatch() {
                    if (confirmPasswordInput.value !== newPasswordInput.value) {
                        confirmPasswordInput.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPasswordInput.setCustomValidity('');
                    }
                }

                newPasswordInput.addEventListener('change', validatePasswordMatch);
                confirmPasswordInput.addEventListener('input', validatePasswordMatch);
            }
        }

        // Phone number formatting and validation
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                // Remove any non-digit characters
                let value = this.value.replace(/\D/g, '');
                
                // Format the number
                if (value.length > 0) {
                    if (value.length <= 11) { // For BD numbers
                        if (value.length > 6) {
                            value = value.replace(/(\d{5})(\d{1,6})/, '$1-$2');
                        } else if (value.length > 3) {
                            value = value.replace(/(\d{3})(\d{1,3})/, '$1-$2');
                        }
                    }
                }
                
                this.value = value;
                
                // Validate phone number
                const isValid = value.length >= 10 && value.length <= 11;
                this.setCustomValidity(isValid ? '' : 'Please enter a valid phone number');
            });
        }

        // Date of Birth validation
        if (dobInput) {
            dobInput.addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                if (age < 18) {
                    this.setCustomValidity('You must be at least 18 years old');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        // NID validation
        if (nidInput) {
            nidInput.addEventListener('input', function() {
                // Remove any non-digit characters
                this.value = this.value.replace(/\D/g, '');
                
                // Validate NID length (assuming Bangladesh NID which is 10 or 13 or 17 digits)
                const isValid = this.value.length === 0 || // Allow empty as it's optional
                            this.value.length === 10 || 
                            this.value.length === 13 || 
                            this.value.length === 17;
                            
                this.setCustomValidity(isValid ? '' : 'Please enter a valid NID number');
            });
        }

        // Form submission handling
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                this.classList.add('was-validated');
            });
        });

        // Add animation effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });

            // Initialize with focused class if has value
            if (input.value) {
                input.parentElement.classList.add('focused');
            }
        });

        // Success message auto-hide
        const successAlerts = document.querySelectorAll('.alert-success');
        successAlerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        });
    });
    </script>

</body>

</html>