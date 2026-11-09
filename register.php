<?php
	require_once 'functions/HealthSystem.php';

	$healthSystem = new HealthSystem($pdo);

	if(isset($_POST['register_btn'])){
		$username = $_POST['username'];
		$password = $_POST['password'];
		$firstname = $_POST['firstname']; 
		$lastname = $_POST['lastname'];

		$result = $healthSystem->register($username, $password, $firstname, $lastname);

		if($result){
			echo "<script>alert('Registered! You can now log in.'); window.location='login.php'; </script>";
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Register</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<h4 class="card-title mb-3 text-center">Create an Account</h4>

					<form method="post">
						<div class="mb-3">
							<label class="form-label">First Name</label>
							<input type="text" name="firstname" class="form-control" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Last Name</label>
							<input type="text" name="lastname" class="form-control" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Username</label>
							<input type="text" name="username" class="form-control" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Password</label>
							<input type="password" name="password" class="form-control" required>
						</div>

						<button type="submit" name="register_btn" class="btn btn-primary w-100">Register</button>
					</form>

					<p class="text-center mt-3 mb-0">Already registered? <a href="login.php">Log in</a></p>
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>