
<?php
session_start();
require_once "HealthSystem.php";

$obj = new HealthSystem($pdo);
$error = "";

if(isset($_POST['login_btn'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $obj->login($username);

    if($result){

        if(password_verify($password, $result['password'])){

            session_regenerate_id(true);

            $_SESSION['ID'] = $result['user_id'];
            $_SESSION['USERNAME'] = $result['username'];
            $_SESSION['ROLE'] = $result['role'];

            if($result['role'] == 'admin'){
                header("location:admin/index.php");
                exit();
            }
            else if($result['role'] == 'bhw'){
                header("location:bhw/index.php");
                exit();
            }
            else if($result['role'] == 'nurse'){
                header("location:nurse/index.php");
                exit();
            }
            else if($result['role'] == 'mho'){
                header("location:mho/index.php");
                exit();
            }
            else if($result['role'] == 'resident'){
                header("location:resident/index.php");
                exit();
            }
            else{
                $error = "Invalid account role.";
            }
        }
        else{
            $error = "Invalid username or password.";
        }
    }
    else{
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 95%;
            max-width: 500px;
            border-radius: 12px;
            overflow: hidden;
            background-color: white;
        }

        .login-header {
            background-color: #123B70;
            color: white;
            padding: 35px;
            text-align: center;
        }

        .login-header img {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
        }

        .login-card .card-body {
            padding: 35px !important;

            background-image:
                linear-gradient(
                    rgba(255,255,255,0.88),
                    rgba(255,255,255,0.88)
                ),
                url("brgylogo.png");

            background-repeat: no-repeat;
            background-position: center;
            background-size: 350px;
            background-color: white;
        }

        .login-title {
            color: #123B70;
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-label {
            font-size: 15px;
        }

        .form-control {
            padding: 12px;
            font-size: 15px;
        }

        .login-btn {
            background-color: #123B70;
            border-color: #123B70;
            color: white;
            padding: 12px;
            font-size: 16px;
        }

        .login-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        .login-links a {
            color: #123B70;
            text-decoration: none;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        @media(max-width: 600px){
            .login-card .card-body {
                padding: 25px !important;
                background-size: 280px;
            }
        }
    </style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><style>.password-wrap{position:relative}.password-wrap input{padding-right:48px}.password-eye{position:absolute;right:4px;top:50%;transform:translateY(-50%);width:44px;height:44px;border:0;background:transparent;color:#123B70;font-size:20px}</style></head>

<body>

<div class="card login-card shadow-sm border-0">

    <div class="login-header">

        <img src="brgylogo.png" alt="Barangay Tawang Logo">

        <h3 class="mb-1">Welcome Back!</h3>

        <p class="mb-0">Barangay Tawang Health System</p>

    </div>

    <div class="card-body">

        <h4 class="login-title">Log In</h4>

        <?php if($error != ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                Registration successful! Please log in.
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Email / Username</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="password-wrap"><input type="password" id="password"
                       name="password"
                       class="form-control"
                       required><button type="button" class="password-eye" aria-label="Show password" onclick="togglePassword(this)"><i class="bi bi-eye"></i></button></div>
            </div>

            <button type="submit"
                    name="login_btn"
                    class="btn login-btn w-100">
                Log In
            </button>

        </form>

        <div class="login-links text-center mt-3">

            <p class="mb-2">
                Don't have an account?
                <a href="register.php">Register</a>
            </p>

            <a href="index.php">Back to Home</a>

        </div>

    </div>
</div>

<script>function togglePassword(b){const p=b.parentElement.querySelector("input");p.type=p.type==="password"?"text":"password";b.querySelector("i").className=p.type==="text"?"bi bi-eye-slash":"bi bi-eye";b.setAttribute("aria-label",p.type==="text"?"Hide password":"Show password");}</script></body>
</html>
