
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BHW Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Barangay Tawang Health System | BHW
        </a>

        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="residents.php">Residents</a>
            <a class="nav-link" href="consultations.php">Consultations</a>
            <a class="nav-link" href="vaccinations.php">Vaccinations</a>
            <a class="nav-link" href="maternal_child.php">Maternal & Child</a>
            <a class="btn btn-outline-light ms-2" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

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

</main>

</body>
</html>
