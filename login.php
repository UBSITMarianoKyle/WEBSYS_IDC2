<?php
session_start();
require_once __DIR__ . "/dbconn.php";
require_once __DIR__ . "/functions/HealthSystem.php";
$healthSystem = new HealthSystem($pdo);

$rolePages = [
	"admin" => "admin/index.php",
	"bhw" => "bhw/index.php",
	"nurse" => "nurse/index.php",
	"mho" => "mho/index.php",
	"resident" => "resident/index.php",
];

$sessionRole = $_SESSION["ROLE"] ?? null;
if (is_string($sessionRole) && isset($rolePages[$sessionRole])) {
	header("Location: " . $rolePages[$sessionRole]);
	exit;
}

if (!isset($_SESSION["csrf_token"])) {
	$_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$error = null;
$success = $_SESSION["registration_success"] ?? null;
unset($_SESSION["registration_success"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$submittedToken = $_POST["csrf_token"] ?? "";
	if (!is_string($submittedToken) || !hash_equals($_SESSION["csrf_token"], $submittedToken)) {
		http_response_code(403);
		$error = "The form expired. Please reload the page and try again.";
	} elseif (time() < ($_SESSION["lock_until"] ?? 0)) {
		$error = "Too many failed attempts. Please wait a minute and try again.";
	} else {
		if (isset($_SESSION["lock_until"])) {
			unset($_SESSION["fails"], $_SESSION["lock_until"]);
		}

		$username = trim((string) ($_POST["username"] ?? ""));
		$password = (string) ($_POST["password"] ?? "");
		$user = $healthSystem->login($username, $password);

		if (
			$user
			&& isset($rolePages[$user["role"] ?? ""])
		) {
			session_regenerate_id(true);
			$_SESSION["USER_ID"] = $user["id"] ?? null;
			$_SESSION["USERNAME"] = $user["username"];
			$_SESSION["FNAME"] = $user["firstname"] ?? $user["resident_first_name"] ?? "";
			$_SESSION["LNAME"] = $user["lastname"] ?? $user["resident_last_name"] ?? "";
			$_SESSION["ROLE"] = $user["role"];
			$_SESSION["RESIDENT_ID"] = $user["resident_id"] ?? null;
			unset($_SESSION["fails"], $_SESSION["lock_until"]);
			$_SESSION["csrf_token"] = bin2hex(random_bytes(32));
			header("Location: " . $rolePages[$user["role"]]);
			exit;
		}

		$_SESSION["fails"] = ($_SESSION["fails"] ?? 0) + 1;
		if ($_SESSION["fails"] >= 5) {
			$_SESSION["lock_until"] = time() + 60;
			$_SESSION["fails"] = 5;
		}
		$error = "Invalid username or password.";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<title>Login | Barangay Ambiong Health System</title>
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
<main class="container">
	<div class="row justify-content-center">
		<div class="col-md-5 col-lg-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<h1 class="h4 mb-2 text-center">Barangay Ambiong Health System</h1>
					<p class="text-muted text-center">Ambiong, La Trinidad, Benguet</p>
					<?php if ($success !== null): ?>
						<div class="alert alert-success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?></div>
					<?php endif; ?>
					<?php if ($error !== null): ?>
						<div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
					<?php endif; ?>
					<form method="post">
						<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>">
						<div class="mb-3">
							<label class="form-label" for="username">Username</label>
							<input class="form-control" id="username" name="username" autocomplete="username" required>
						</div>
						<div class="mb-3">
							<label class="form-label" for="password">Password</label>
							<input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
						</div>
						<button class="btn btn-primary w-100" type="submit">Login</button>
					</form>
					<p class="text-center mt-3 mb-0">
						<a href="register.php">Register as a resident</a> &middot;
						<a href="index.php">Back to home</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</main>
</body>
</html>
