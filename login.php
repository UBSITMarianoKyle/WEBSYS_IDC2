<?php
    session_start();
    require_once 'functions/HealthSystem.php';

    $healthSystem = new HealthSystem($pdo);

    if(isset($_POST['login_btn'])){
        $username = $_POST['username'];
        $password = $_POST['password'];

        $result = $healthSystem->login($username);

        if(!$result){
            echo "<script>alert('No accounts');</script>";
        }
        else{
            if(password_verify($password, $result['password']) && $result['role'] == "admin"){
                $_SESSION["ID"] = $result['user_id'];
                $_SESSION["USERNAME"] = $result['username'];
                $_SESSION["ROLE"] = $result['role'];

                header("location:admin/index.php");
                exit;
            }
            else if(password_verify($password, $result['password']) && $result['role'] == "bhw"){
                $_SESSION["ID"] = $result['user_id'];
                $_SESSION["USERNAME"] = $result['username'];
                $_SESSION["ROLE"] = $result['role'];

                header("location:bhw/index.php");
                exit;
            }
            else if(password_verify($password, $result['password']) && $result['role'] == "nurse"){
                $_SESSION["ID"] = $result['user_id'];
                $_SESSION["USERNAME"] = $result['username'];
                $_SESSION["ROLE"] = $result['role'];

                header("location:nurse/index.php");
                exit;
            }
            else if(password_verify($password, $result['password']) && $result['role'] == "mho"){
                $_SESSION["ID"] = $result['user_id'];
                $_SESSION["USERNAME"] = $result['username'];
                $_SESSION["ROLE"] = $result['role'];

                header("location:mho/index.php");
                exit;
            }
            else if(password_verify($password, $result['password']) && $result['role'] == "resident"){
                $_SESSION["ID"] = $result['user_id'];
                $_SESSION["USERNAME"] = $result['username'];
                $_SESSION["ROLE"] = $result['role'];

                header("location:resident/index.php");
                exit;
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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Log In</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<h4 class="card-title mb-3 text-center">Log In</h4>

					<form method="post">
						<div class="mb-3">
							<label class="form-label">Username</label>
							<input type="text" name="username" class="form-control" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Password</label>
							<input type="password" name="password" class="form-control" required>
						</div>

						<button type="submit" name="login_btn" class="btn btn-primary w-100">Log In</button>
					</form>

					<p class="text-center mt-3 mb-0">Don't have an account? <a href="register.php">Register</a></p>
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>

