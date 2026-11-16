<?php
    session_start();
    require_once __DIR__ . "/HealthSystem.php";

    $healthSystem = new HealthSystem($pdo);

    if(isset($_POST['login_btn'])){
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $result = $healthSystem->login($username);

        if(!$result){
            echo "<script>alert('No active account found');</script>";
        }
        else{
            if(password_verify($password, $result['password'])){

                session_regenerate_id(true);

                $_SESSION["ID"] = $result['user_id'];
                $_SESSION["USERNAME"] = $result['username'];
                $_SESSION["ROLE"] = $result['role'];

                if($result['role'] == "admin"){
                    header("location:admin/index.php");
                    exit;
                }
                else if($result['role'] == "bhw"){
                    header("location:bhw/index.php");
                    exit;
                }
                else if($result['role'] == "nurse"){
                    header("location:nurse/index.php");
                    exit;
                }
                else if($result['role'] == "mho"){
                    header("location:mho/index.php");
                    exit;
                }
                else if($result['role'] == "resident"){
                    header("location:resident/index.php");
                    exit;
                }
                else{
                    session_unset();
                    echo "<script>alert('Invalid account role');</script>";
                }
            }
            else{
                echo "<script>alert('Wrong password');</script>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Log In | Barangay Tawang Health System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css">

</head>

<body class="login-page">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">

            <div class="card login-card shadow-sm">

                <div class="login-header">

                    <img src="brgylogo.jpg"
                         alt="Barangay Tawang Logo"
                         class="login-logo"
                         width="80"
                         height="80">

                    <h3>Welcome Back!</h3>

                    <p class="mb-0">
                        Barangay Tawang Health System
                    </p>

                </div>

                <div class="card-body p-4">

                    <h4 class="card-title mb-3 text-center">
                        Log In
                    </h4>

                    <form method="post">

                        <div class="mb-3">
                            <label class="form-label">
                                Email / Username
                            </label>

                            <input type="text"
                                   name="username"
                                   class="form-control"
                                   autocomplete="username"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Password
                            </label>

                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   autocomplete="current-password"
                                   required>
                        </div>

                        <button type="submit"
                                name="login_btn"
                                class="btn login-btn w-100">
                            Log In
                        </button>

                    </form>

                    <p class="text-center mt-3 mb-0">
                        Don't have an account?
                        <a href="register.php">Register</a>
                    </p>

                    <p class="text-center mt-2 mb-0">
                        <a href="index.php">Back to Home</a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
