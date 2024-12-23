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
    <title>CLIX: Edit User</title>

    <!-- css -->
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
// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success_message = '';
$error_message = '';

// Fetch user data
try {
    $stmt = $conn->prepare("
        SELECT u.*, 
        CASE WHEN bc._banned_client_id_ IS NOT NULL THEN 1 ELSE 0 END as is_banned
        FROM user_table u
        LEFT JOIN banned_client_table bc ON u._user_id_ = bc._banned_client_id_
        WHERE u._user_id_ = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!$user_data) {
        header("Location: ./admin-user-control.php");
        exit();
    }

    // Fetch Picture
    if (!empty($user_data['_profile_picture_'])) {
        $base64Image = base64_encode($user_data['_profile_picture_']);
        $imageSrcUser = 'data:image/jpeg;base64,' . $base64Image;
    } else {
        $imageSrcUser = "../img/user-rounded-svgrepo-com.jpg";
    }

} catch (Exception $e) {
    $error_message = "Error fetching user data: " . $e->getMessage();
}

// Handle User Update
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
            throw new Exception("User must be at least 18 years old");
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
            throw new Exception("Error updating user profile: " . $stmt->error);
        }

        // Handle Banning
        if (isset($_POST['toggle_ban'])) {
            $is_banned = $_POST['is_banned'] ? '1' : '0';
            if ($is_banned === '1') {
                // Add to banned_client_table
                $ban_stmt = $conn->prepare("INSERT IGNORE INTO banned_client_table (_banned_client_id_) 
                                            SELECT _client_id_ FROM client_table 
                                            WHERE _client_id_ = ?");
                $ban_stmt->bind_param("i", $user_id);
                $ban_stmt->execute();
            } else {
                // Remove from banned_client_table
                $unban_stmt = $conn->prepare("DELETE FROM banned_client_table WHERE _banned_client_id_ = ?");
                $unban_stmt->bind_param("i", $user_id);
                $unban_stmt->execute();
            }
        }

        $conn->commit();
        $success_message = "User profile updated successfully!";

        // Refresh user data
        $stmt = $conn->prepare("
            SELECT u.*, 
            CASE WHEN bc._banned_client_id_ IS NOT NULL THEN 1 ELSE 0 END as is_banned
            FROM user_table u
            LEFT JOIN banned_client_table bc ON u._user_id_ = bc._banned_client_id_
            WHERE u._user_id_ = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();

        // Update image source
        if (!empty($user_data['_profile_picture_'])) {
            $base64Image = base64_encode($user_data['_profile_picture_']);
            $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
        }

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<main id="main-section">
    <div class="container pb-3">
        <h2>Edit User Details</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- Profile Picture -->
                    <div class="profile-picture-container mb-4">
                        <img id="profilePreview" class="profile-picture"
                             src="<?php echo $imageSrcUser; ?>"
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

                    <!-- Name Fields -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   value="<?php echo htmlspecialchars($user_data['_first_name_']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   value="<?php echo htmlspecialchars($user_data['_last_name_']); ?>" required>
                        </div>
                    </div>

                    <!-- Date of Birth and NID -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                   value="<?php echo htmlspecialchars($user_data['_date_of_birth_']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nid" class="form-label">NID</label>
                            <input type="number" class="form-control" id="nid" name="nid"
                                   value="<?php echo htmlspecialchars($user_data['_nid_'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($user_data['_email_']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($user_data['_phone_']); ?>" required>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="current_address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="current_address" name="current_address"
                               value="<?php echo htmlspecialchars($user_data['_current_address_']); ?>" required>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Update User Details</button>

                        <input type="hidden" name="is_banned"
                               value="<?php echo $user_data['is_banned'] ? '0' : '1'; ?>">
                        <button type="submit" name="toggle_ban"
                                class="btn <?php echo $user_data['is_banned'] ? 'btn-success' : 'btn-danger'; ?>">
                            <?php echo $user_data['is_banned'] ? 'Unban User' : 'Ban User'; ?>
                        </button>
                    </div>
                </form>
            </div>
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
