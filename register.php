<?php
session_start();
require_once __DIR__ . "/config/dbconn.php";

if (!isset($_SESSION["csrf_token"])) {
	$_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$householdQuery = $pdo->query(
	"SELECT id, CONCAT(household_no, ' - ', purok) AS label FROM households ORDER BY household_no"
);
$households = $householdQuery->fetchAll();
$error = null;
$form = [
	"household_id" => "",
	"first_name" => "",
	"last_name" => "",
	"birthdate" => "",
	"sex" => "",
	"contact" => "",
	"emergency_name" => "",
	"emergency_relation" => "",
	"emergency_contact" => "",
	"username" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	foreach ($form as $field => $value) {
		if (isset($_POST[$field]) && is_string($_POST[$field])) {
			$form[$field] = trim($_POST[$field]);
		}
	}

	$submittedToken = $_POST["csrf_token"] ?? "";
	if (!is_string($submittedToken) || !hash_equals($_SESSION["csrf_token"], $submittedToken)) {
		http_response_code(403);
		$error = "The form expired. Please reload the page and try again.";
	} elseif (
		$form["household_id"] === ""
		|| !ctype_digit($form["household_id"])
		|| (int) $form["household_id"] < 1
		|| $form["first_name"] === ""
		|| $form["last_name"] === ""
		|| $form["birthdate"] === ""
		|| !in_array($form["sex"], ["Male", "Female"], true)
		|| $form["emergency_name"] === ""
		|| $form["emergency_relation"] === ""
		|| $form["emergency_contact"] === ""
		|| $form["username"] === ""
	) {
		$error = "Please complete all required fields with valid values.";
	} elseif (
		!DateTime::createFromFormat("!Y-m-d", $form["birthdate"])
		|| DateTime::createFromFormat("!Y-m-d", $form["birthdate"])->format("Y-m-d") !== $form["birthdate"]
	) {
		$error = "Please enter a valid birthdate.";
	} elseif (strlen((string) ($_POST["password"] ?? "")) < 6) {
		$error = "Password must be at least 6 characters.";
	} else {
		$password = (string) $_POST["password"];
		try {
			$pdo->beginTransaction();
			$residentQuery = $pdo->prepare(
				"INSERT INTO residents
					(household_id, first_name, last_name, birthdate, sex, contact, emergency_name, emergency_relation, emergency_contact)
				 VALUES
					(:household_id, :first_name, :last_name, :birthdate, :sex, :contact, :emergency_name, :emergency_relation, :emergency_contact)"
			);
			$residentQuery->execute([
				"household_id" => (int) $form["household_id"],
				"first_name" => $form["first_name"],
				"last_name" => $form["last_name"],
				"birthdate" => $form["birthdate"],
				"sex" => $form["sex"],
				"contact" => $form["contact"] !== "" ? $form["contact"] : null,
				"emergency_name" => $form["emergency_name"],
				"emergency_relation" => $form["emergency_relation"],
				"emergency_contact" => $form["emergency_contact"],
			]);
			$residentId = $pdo->lastInsertId();

			$userQuery = $pdo->prepare(
				"INSERT INTO users (username, password, full_name, role, resident_id)
				 VALUES (:username, :password, :full_name, 'resident', :resident_id)"
			);
			$userQuery->execute([
				"username" => $form["username"],
				"password" => password_hash($password, PASSWORD_DEFAULT),
				"full_name" => $form["first_name"] . " " . $form["last_name"],
				"resident_id" => $residentId,
			]);
			$pdo->commit();

			$_SESSION["registration_success"] = "Account created. You can now log in.";
			header("Location: login.php");
			exit;
		} catch (PDOException $exception) {
			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}

			if ($exception->getCode() === "23000") {
				$error = "That username is already taken, or the selected household is no longer available.";
			} else {
				error_log("Resident registration failed: " . $exception->getMessage());
				$error = "Registration could not be completed. Please try again later.";
			}
		}
	}
}

$escape = static function ($value): string {
	return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<title>Resident Registration | Barangay Ambiong Health System</title>
</head>
<body class="bg-light">
<main class="container py-5">
	<div class="row justify-content-center">
		<div class="col-lg-9">
			<div class="card shadow-sm">
				<div class="card-body p-4">
					<h1 class="h3 mb-3">Resident Registration</h1>
					<p class="text-muted">Your household must already be listed. If it is not, ask your barangay health worker.</p>
					<?php if ($error !== null): ?>
						<div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
					<?php endif; ?>
					<?php if (!$households): ?>
						<div class="alert alert-warning" role="alert">No households are available for registration yet. Please contact your barangay health worker.</div>
					<?php endif; ?>
					<form method="post">
						<input type="hidden" name="csrf_token" value="<?= $escape($_SESSION["csrf_token"]) ?>">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label" for="first_name">First name</label>
								<input class="form-control" id="first_name" name="first_name" value="<?= $escape($form["first_name"]) ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="last_name">Last name</label>
								<input class="form-control" id="last_name" name="last_name" value="<?= $escape($form["last_name"]) ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="birthdate">Birthdate</label>
								<input class="form-control" type="date" id="birthdate" name="birthdate" value="<?= $escape($form["birthdate"]) ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="sex">Sex</label>
								<select class="form-select" id="sex" name="sex" required>
									<option value="">-- select --</option>
									<option value="Male" <?= $form["sex"] === "Male" ? "selected" : "" ?>>Male</option>
									<option value="Female" <?= $form["sex"] === "Female" ? "selected" : "" ?>>Female</option>
								</select>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="household_id">Household</label>
								<select class="form-select" id="household_id" name="household_id" required>
									<option value="">-- select --</option>
									<?php foreach ($households as $household): ?>
										<option value="<?= $escape($household["id"]) ?>" <?= $form["household_id"] === (string) $household["id"] ? "selected" : "" ?>>
											<?= $escape($household["label"]) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="contact">Contact no.</label>
								<input class="form-control" id="contact" name="contact" value="<?= $escape($form["contact"]) ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label" for="emergency_name">Emergency contact name</label>
								<input class="form-control" id="emergency_name" name="emergency_name" value="<?= $escape($form["emergency_name"]) ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="emergency_relation">Relationship</label>
								<input class="form-control" id="emergency_relation" name="emergency_relation" value="<?= $escape($form["emergency_relation"]) ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="emergency_contact">Emergency contact no.</label>
								<input class="form-control" id="emergency_contact" name="emergency_contact" value="<?= $escape($form["emergency_contact"]) ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="username">Username</label>
								<input class="form-control" id="username" name="username" value="<?= $escape($form["username"]) ?>" autocomplete="username" required>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="password">Password</label>
								<input class="form-control" type="password" id="password" name="password" minlength="6" autocomplete="new-password" required>
							</div>
						</div>
						<div class="d-flex justify-content-between align-items-center mt-4">
							<a href="login.php">Cancel</a>
							<button class="btn btn-primary" type="submit">Create Account</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</main>
</body>
</html>
