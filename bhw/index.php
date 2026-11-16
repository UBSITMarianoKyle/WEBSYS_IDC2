
<?php
session_start();
require_once "../HealthSystem.php";

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "bhw") {
    header("Location: ../login.php");
    exit();
}

$healthSystem = new HealthSystem($pdo);
$stats = $healthSystem->dashboard_stats();

function h($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>BHW Dashboard | BHW</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f8f9fa;font-family:Arial,sans-serif}.navbar{background:#ffeb00;border-bottom:3px solid #123b70;padding:10px 20px}.navbar-brand{color:#123b70;font-size:18px;font-weight:bold}.barangay-logo{width:45px;height:45px;border-radius:50%;object-fit:cover}.logout-btn{border:1px solid #123b70;color:#123b70}.logout-btn:hover{background:#123b70;color:white}.sidebar{background:#123b70;min-height:calc(100vh - 73px);padding:20px 15px}.sidebar h5{color:white;font-size:18px;font-weight:bold;margin-bottom:25px}.sidebar a{display:block;color:white;text-decoration:none;padding:12px 15px;margin-bottom:8px;border-radius:6px;font-size:15px}.sidebar a:hover{background:#244f83;color:white}.sidebar a.active{background:#ffeb00;color:#123b70;font-weight:bold}.main-content{padding:25px}.welcome-header{background:#123b70;color:white;padding:25px;border-radius:10px;margin-bottom:25px}.section-title{color:#123b70;font-size:23px;font-weight:bold}.card{border:1px solid #e9ecef;border-radius:10px}.card-header{background:#f1f5fa;color:#123b70}.btn-primary{background:#123b70;border-color:#123b70}.btn-primary:hover{background:#244f83}.table thead th{background:#123b70;color:white}</style></head><body>
<nav class="navbar d-flex align-items-center"><a class="navbar-brand d-flex align-items-center gap-2" href="index.php"><img class="barangay-logo" src="../logo/barangay ambiong health center logo.jpg" alt="Barangay logo">Barangay Tawang Health System | BHW</a><a href="../logout.php" class="btn logout-btn ms-auto">Logout</a></nav>
<div class="container-fluid"><div class="row"><aside class="col-md-3 col-lg-2 sidebar"><h5>BHW Menu</h5><a class="active" href="index.php">Dashboard</a>
<a class="" href="residents.php">Residents</a>
<a class="" href="consultations.php">Consultations</a>
<a class="" href="vaccinations.php">Vaccinations</a>
<a class="" href="maternal_child.php">Maternal & Child</a></aside><main class="col-md-9 col-lg-10 main-content"><h2 class="section-title mb-4">BHW Dashboard</h2>


<header class="hero-section">
    <div class="container">
        <h1>BHW Dashboard</h1>

        <p>
            Welcome, <?= h($_SESSION["USERNAME"]) ?>!
            Manage residents and barangay health services.
        </p>

        <a href="residents.php" class="btn btn-light">
            View Residents
        </a>

        <a href="consultations.php" class="btn btn-outline-light">
            Record Consultation
        </a>
    </div>
</header>

<main class="container py-4">

    <h2 class="section-title">Health System Overview</h2>

    <div class="row g-3 mt-2">

        <?php
        $cards = [
            "Total Residents" => $stats["total_residents"],
            "Consultations" => $stats["total_consultations"],
            "Vaccinations" => $stats["total_vaccinations"],
            "Ongoing Pregnancies" => $stats["ongoing_pregnancies"],
            "Child Health Records" => $stats["child_health_records"],
            "Pending Appointments" => $stats["pending_appointments"]
        ];

        foreach ($cards as $title => $number):
        ?>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted"><?= h($title) ?></p>
                    <h2 class="text-primary">
                        <?= h($number) ?>
                    </h2>
                </div>
            </div>
        </div>

        <?php endforeach; ?>

    </div>

    <h2 class="section-title mt-4">BHW Activities</h2>

    <div class="row g-3">

        <div class="col-md-6">
            <a href="residents.php" class="card card-body text-decoration-none">
                <h5>Resident Records</h5>
                <p class="text-muted">View registered residents and their information.</p>
            </a>
        </div>

        <div class="col-md-6">
            <a href="consultations.php" class="card card-body text-decoration-none">
                <h5>Consultations</h5>
                <p class="text-muted">Record resident symptoms and treatments.</p>
            </a>
        </div>

        <div class="col-md-6">
            <a href="vaccinations.php" class="card card-body text-decoration-none">
                <h5>Vaccinations</h5>
                <p class="text-muted">Manage vaccination records and schedules.</p>
            </a>
        </div>

        <div class="col-md-6">
            <a href="maternal_child.php" class="card card-body text-decoration-none">
                <h5>Maternal and Child Health</h5>
                <p class="text-muted">Monitor pregnancy and child health records.</p>
            </a>
        </div>

    </div>

</main></div></div></body></html>