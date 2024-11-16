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
        <form>
            <h1 class="h3 mt-4 mb-4 fw-normal text-center" id="sign-h1">Sign In</h1>
            <div class="form-floating">
                <input class="form-control" id="floatingInput" placeholder="1234567890">
                <label for="floatingInput" id="floatingInputText">NID</label>
            </div>
            <div class="form-floating mt-3">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword" id="floatingPasswordText">Password</label>
            </div>
            <div class="form-check text-start my-3 d-flex justify-content-between align-items-center">
                <div>
                    <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                    <label class="form-check-label" for="flexCheckDefault" id="rem-lab">Remember me</label>
                </div>
                <a class="sign-up" href="../index.php">New Here? Sign Up</a>
            </div>
            <a href="../index.php" class="btn btn-primary w-100 mt-3 mb-4" role="button">Sign in</a>
        </form>
    </main>
</body>

</html>