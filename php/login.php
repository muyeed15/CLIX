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
            <h1 class="h3 mb-3 fw-normal text-center" id="sign-h1">Sign In</h1>
            <div class="form-floating">
                <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                <label for="floatingInput" id="floatingInputText">Email address</label>
            </div>
            <div class="form-floating mt-3">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword" id="floatingPasswordText">Password</label>
            </div>
            <div class="form-check text-start my-3">
                <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault" id="rem-lab">Remember me</label>
            </div>
            <a href="../index.php" class="btn btn-primary w-100 py-2" role="button">Sign in</a>
            <p class="mt-5 mb-3 text-body-secondary text-center">CLIX Inc. ©2024</p>
        </form>
    </main>
</body>

</html>