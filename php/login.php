<?php
session_start();
require_once 'db-connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nid = $_POST['nid'];
    $password = $_POST['password'];

    $query = "SELECT * FROM user_t WHERE _nid_ = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "s", $nid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['_password_'])) {
            $_SESSION['nid'] = $user['_nid_'];
            $_SESSION['name'] = $user['_first_name_'] . ' ' . $user['_last_name_'];
            $_SESSION['email'] = $user['_email_'];

            header("Location: dashboard.php");
            exit;
        } elseif ($user['_password_'] === $password) {
            $_SESSION['nid'] = $user['_nid_'];
            $_SESSION['name'] = $user['_first_name_'] . ' ' . $user['_last_name_'];
            $_SESSION['email'] = $user['_email_'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid NID or Password!";
        }
    } else {
        $error = "Invalid NID or Password!";
    }
}
?>

<!doctype html>

<!-- html -->
<html lang="en">

<!-- head -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Login</title>

    <!-- css -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="../vid/6922963-hd_1920_1080_25fps.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div id="header-logo-container">
        <a href="#">
            <img class="py-2" src="../img/CLIX.svg" id="header-logo" alt="Logo">
        </a>
    </div>

    <main class="form-signin w-100 m-auto">
        <form method="POST" action="">
            <h1 class="h3 mt-4 mb-4 fw-normal text-center" id="sign-h1">Sign In</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-floating">
                <input name="nid" type="text" class="form-control" id="floatingInput" placeholder="1234567890" required>
                <label for="floatingInput" id="floatingInputText">NID</label>
            </div>
            <div class="form-floating mt-3">
                <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword" id="floatingPasswordText">Password</label>
            </div>
            <div class="form-check text-start my-3 d-flex justify-content-between align-items-center">
                <div>
                    <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                    <label class="form-check-label" for="flexCheckDefault" id="rem-lab">Remember me</label>
                </div>
                <a class="sign-up" href="../index.php">New Here? Sign Up</a>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3 mb-4">Sign in</button>
        </form>
    </main>
</body>

</html>
