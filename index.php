<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<title>Welcome | Barangay Ambiong Health System</title>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
	<div class="container">
		<a class="navbar-brand fw-semibold" href="index.php">Barangay Ambiong Health System</a>
		<div class="d-flex gap-2">
			<a class="btn btn-outline-light" href="login.php">Login</a>
			<a class="btn btn-light text-success" href="register.php">Register</a>
		</div>
	</div>
</nav>
<main>
	<section class="bg-success text-white py-5">
		<div class="container py-lg-4">
			<p class="text-uppercase fw-semibold mb-2">Barangay Ambiong, La Trinidad, Benguet</p>
			<h1 class="display-5 fw-bold">Health services and community health monitoring</h1>
			<p class="lead col-lg-8 mt-3">Keep resident health records, vaccinations, maternal and child care, medicine stock, and clinic appointments in one place.</p>
			<div class="d-flex flex-wrap gap-2 mt-4">
				<a class="btn btn-light btn-lg text-success" href="login.php">Login</a>
				<a class="btn btn-outline-light btn-lg" href="register.php">Register as a Resident</a>
			</div>
		</div>
	</section>
	<section class="container py-5">
		<h2 class="h3 mb-4">Health services</h2>
		<div class="row g-3">
			<div class="col-md-6 col-lg-4"><article class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">Resident Registration</h3><p class="card-text text-body-secondary">Household and resident records with health profiles and emergency contacts.</p></div></article></div>
			<div class="col-md-6 col-lg-4"><article class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">Consultations</h3><p class="card-text text-body-secondary">Symptoms, health worker assessment, and consultation history.</p></div></article></div>
			<div class="col-md-6 col-lg-4"><article class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">Vaccination</h3><p class="card-text text-body-secondary">Vaccine records, upcoming schedules, and completion status.</p></div></article></div>
			<div class="col-md-6 col-lg-4"><article class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">Maternal and Child Health</h3><p class="card-text text-body-secondary">Pregnancy registry, prenatal visits, and child growth monitoring.</p></div></article></div>
			<div class="col-md-6 col-lg-4"><article class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">Medicine Inventory</h3><p class="card-text text-body-secondary">Stock movements, distribution records, and low-stock alerts.</p></div></article></div>
			<div class="col-md-6 col-lg-4"><article class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">Appointments</h3><p class="card-text text-body-secondary">Request clinic visits and receive an approved schedule.</p></div></article></div>
		</div>
	</section>
	<section class="bg-white border-top py-4">
		<div class="container">
			<h2 class="h4">Who uses the system</h2>
			<p class="mb-1">System administrators, barangay health workers, health center nurses, municipal health officers, residents, and patients.</p>
			<p class="text-body-secondary mb-0">Staff accounts are created by the System Administrator. Residents can register their own account.</p>
		</div>
	</section>
</main>
<footer class="container py-4 text-center text-body-secondary">
	Barangay Ambiong, La Trinidad, Benguet &middot; Health Services and Community Health Monitoring System
</footer>
</body>
</html>
