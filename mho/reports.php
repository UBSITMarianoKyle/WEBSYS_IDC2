<?php
session_start();
require_once __DIR__ . '/../HealthSystem.php';
if (!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') !== 'mho') {
    header('Location: ../login.php');
    exit;
}
$obj = new HealthSystem($pdo);
$message = '';
$error = '';
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$counts = [];
foreach (['residents','consultations','vaccinations','pregnancies','prenatal_visits','child_health','health_visits'] as $table) {
    $counts[$table] = (int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
}
$consultations = array_slice($obj->show_consultations(), 0, 20);
$vaccinations = array_slice($obj->show_vaccinations(), 0, 20);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Health Reports | MHO</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f8f9fa;font-family:Arial,sans-serif}.navbar{background:#ffeb00;border-bottom:3px solid #123b70;padding:10px 20px}.navbar-brand{color:#123b70;font-size:18px;font-weight:bold}.barangay-logo{width:45px;height:45px;border-radius:50%;object-fit:cover}.logout-btn{border:1px solid #123b70;color:#123b70}.logout-btn:hover{background:#123b70;color:white}.sidebar{background:#123b70;min-height:calc(100vh - 73px);padding:20px 15px}.sidebar h5{color:white;font-size:18px;font-weight:bold;margin-bottom:25px}.sidebar a{display:block;color:white;text-decoration:none;padding:12px 15px;margin-bottom:8px;border-radius:6px;font-size:15px}.sidebar a:hover{background:#244f83;color:white}.sidebar a.active{background:#ffeb00;color:#123b70;font-weight:bold}.main-content{padding:25px}.welcome-header{background:#123b70;color:white;padding:25px;border-radius:10px;margin-bottom:25px}.section-title{color:#123b70;font-size:23px;font-weight:bold}.card{border:1px solid #e9ecef;border-radius:10px}.card-header{background:#f1f5fa;color:#123b70}.btn-primary{background:#123b70;border-color:#123b70}.btn-primary:hover{background:#244f83}.table thead th{background:#123b70;color:white}</style><link rel="stylesheet" href="../mobile_additions.css"></head><body>
<nav class="navbar d-flex align-items-center"><button type="button" class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" onclick="document.body.classList.toggle('menu-open');this.setAttribute('aria-expanded',document.body.classList.contains('menu-open'))">☰</button><a class="navbar-brand d-flex align-items-center gap-2" href="index.php"><img class="barangay-logo" src="../brgylogo.png" alt="Barangay logo">Barangay Tawang Health System | MHO</a><a href="../logout.php" class="btn logout-btn ms-auto">Logout</a></nav>
<div class="container-fluid"><div class="row"><aside class="col-md-3 col-lg-2 sidebar"><h5>MHO Menu</h5><a class="" href="index.php">Dashboard</a>
<a class="active" href="reports.php">Health Reports</a></aside><main class="col-md-9 col-lg-10 main-content"><h2 class="section-title mb-4">Health Reports</h2>
<div class="row g-3 mb-4"><?php foreach ($counts as $name => $number): ?><div class="col-md-4"><div class="card p-3"><h6><?= htmlspecialchars(ucwords(str_replace('_',' ',$name))) ?></h6><h2><?= $number ?></h2></div></div><?php endforeach; ?></div>
<h4>Recent Consultations</h4><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Date</th><th>Resident</th><th>Symptoms</th><th>Assessment</th></tr></thead><tbody><?php foreach ($consultations as $row): ?><tr><td><?= htmlspecialchars($row['consultation_date'] ?? '') ?></td><td><?= htmlspecialchars(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) ?></td><td><?= htmlspecialchars($row['symptoms'] ?? '') ?></td><td><?= htmlspecialchars($row['assessment'] ?? '') ?></td></tr><?php endforeach; ?></tbody></table></div>
<h4>Recent Vaccinations</h4><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Date</th><th>Resident</th><th>Vaccine</th><th>Dose</th></tr></thead><tbody><?php foreach ($vaccinations as $row): ?><tr><td><?= htmlspecialchars($row['vaccination_date'] ?? '') ?></td><td><?= htmlspecialchars(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) ?></td><td><?= htmlspecialchars($row['vaccine_name'] ?? '') ?></td><td><?= htmlspecialchars($row['dose_number'] ?? '') ?></td></tr><?php endforeach; ?></tbody></table></div><?php require __DIR__.'/../analytics_partial.php'; ?></main></div></div></body></html>