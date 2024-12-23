<?php
global $conn, $user_id;
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
    <title>CLIX: Profile</title>

    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/profile.css">

</head>

<!-- body -->

<body>
<!-- header -->
<?php
require_once './admin-header.php';
?>

<!-- main -->
<?php
$success_message = '';
$error_message = '';
$password_success = '';
$password_error = '';

// Update Password
if (isset($_POST['update_password'])) {
    try {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            throw new Exception("All password fields are required");
        }

        // Verify Password
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

// Fetch User Data
try {
    $stmt = $conn->prepare("SELECT * FROM user_table WHERE _user_id_ = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    // Fetch Picture
    if (!empty($user_data['_profile_picture_'])) {
        $base64Image = base64_encode($user_data['_profile_picture_']);
        $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrc = "./img/user-rounded-svgrepo-com.jpg";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn->begin_transaction();

        try {
            // Input Validation
            $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $date_of_birth = trim($_POST['date_of_birth'] ?? '');
            $nid = !empty($_POST['nid']) ? filter_input(INPUT_POST, 'nid', FILTER_SANITIZE_NUMBER_INT) : null;
            $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
            $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
            $current_address = htmlspecialchars(trim($_POST['current_address'] ?? ''), ENT_QUOTES, 'UTF-8');

            // Check Fields
            if (empty($first_name) || empty($last_name) || empty($date_of_birth) ||
                empty($email) || empty($phone) || empty($current_address)) {
                throw new Exception("All required fields must be filled out");
            }

            // Email Format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Phone Format
            if (!preg_match("/^[0-9+\-\s()]*$/", $phone)) {
                throw new Exception("Invalid phone number format");
            }

            // Age Check
            $birthDate = new DateTime($date_of_birth);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            if ($age < 18) {
                throw new Exception("You must be at least 18 years old");
            }

            // Upload Profile Picture
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

            // Email Check
            if ($email !== $user_data['_email_']) {
                $check_email = $conn->prepare("SELECT _email_ FROM user_table WHERE _email_ = ? AND _user_id_ != ?");
                $check_email->bind_param("si", $email, $user_id);
                $check_email->execute();
                if ($check_email->get_result()->num_rows > 0) {
                    throw new Exception("This email is already registered");
                }
            }

            // Phone Check
            if ($phone !== $user_data['_phone_']) {
                $check_phone = $conn->prepare("SELECT _phone_ FROM user_table WHERE _phone_ = ? AND _user_id_ != ?");
                $check_phone->bind_param("si", $phone, $user_id);
                $check_phone->execute();
                if ($check_phone->get_result()->num_rows > 0) {
                    throw new Exception("This phone number is already registered");
                }
            }

            // NID Check
            if ($nid !== null && $nid !== $user_data['_nid_']) {
                $check_nid = $conn->prepare("SELECT _nid_ FROM user_table WHERE _nid_ = ? AND _user_id_ != ?");
                $check_nid->bind_param("ii", $nid, $user_id);
                $check_nid->execute();
                if ($check_nid->get_result()->num_rows > 0) {
                    throw new Exception("This NID is already registered");
                }
            }

            // Update
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

            // Refresh
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
                    <img id="profilePreview" class="profile-picture" src="<?php echo $imageSrc; ?>"
                         alt="Profile Picture">
                    <label for="profilePicture" class="profile-picture-upload">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                    <input type="file" id="profilePicture" name="profile_picture" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-floating">
                        <input name="first_name" type="text" class="form-control" id="floatingFirstName"
                               placeholder="First Name"
                               value="<?php echo htmlspecialchars($user_data['_first_name_']); ?>" required>
                        <label for="floatingFirstName">First Name</label>
                    </div>
                    <div class="form-floating">
                        <input name="last_name" type="text" class="form-control" id="floatingLastName"
                               placeholder="Last Name"
                               value="<?php echo htmlspecialchars($user_data['_last_name_']); ?>" required>
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
                               placeholder="NID Number"
                               value="<?php echo htmlspecialchars($user_data['_nid_'] ?? ''); ?>">
                        <label for="floatingNID">NID (Optional)</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-floating">
                        <input name="email" type="email" class="form-control" id="floatingEmail"
                               placeholder="name@example.com"
                               value="<?php echo htmlspecialchars($user_data['_email_']); ?>" required>
                        <label for="floatingEmail">Email</label>
                    </div>
                    <div class="form-floating">
                        <input name="phone" type="tel" class="form-control" id="floatingPhone"
                               placeholder="Phone Number" value="<?php echo htmlspecialchars($user_data['_phone_']); ?>"
                               required>
                        <label for="floatingPhone">Phone</label>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input name="current_address" type="text" class="form-control" id="floatingAddress"
                           placeholder="Address"
                           value="<?php echo htmlspecialchars($user_data['_current_address_']); ?>" required>
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
                    <input name="confirm_new_password" type="password" class="form-control"
                           id="floatingConfirmNewPassword"
                           placeholder="Confirm New Password" required>
                    <label for="floatingConfirmNewPassword">Confirm New Password</label>
                </div>

                <button type="submit" name="update_password" class="btn btn-warning w-100">Update Password</button>
            </form>
        </div>
    </div>
</main>

<!-- footer -->
<?php
require_once './admin-footer.php';
?>

<!-- script -->
<script src="../js/bootstrap.bundle.js"></script>
<script src="../js/profile.js"></script>

</body>

</html>
