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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>BHW Dashboard | BHW</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* GENERAL */
        body {
            background: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        /* NAVBAR */
        .navbar {
            background: #ffeb00;
            border-bottom: 3px solid #123b70;
            padding: 10px 20px;
        }

        .navbar-brand {
            color: #123b70;
            font-size: 18px;
            font-weight: bold;
        }

        .barangay-logo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .logout-btn {
            border: 1px solid #123b70;
            color: #123b70;
        }

        .logout-btn:hover {
            background: #123b70;
            color: white;
        }

        /* SIDEBAR */
        .sidebar {
            background: #123b70;
            min-height: calc(100vh - 73px);
            padding: 20px 15px;
        }

        .sidebar h5 {
            color: white;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 15px;
        }

        .sidebar a:hover {
            background: #244f83;
            color: white;
        }

        .sidebar a.active {
            background: #ffeb00;
            color: #123b70;
            font-weight: bold;
        }

        /* MAIN CONTENT */
        .main-content {
            padding: 25px;
        }

        .section-title {
            color: #123b70;
            font-size: 23px;
            font-weight: bold;
        }

        /* WELCOME SECTION */
        .hero-section {
            background: #123b70;
            color: white;
            padding: 35px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .hero-section h1 {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .hero-section p {
            font-size: 16px;
            margin-bottom: 22px;
        }

        /* DASHBOARD BUTTONS */
        .action-btn {
            display: inline-block;
            padding: 11px 22px;
            margin-right: 10px;
            margin-bottom: 5px;
            border: 2px solid #ffeb00;
            border-radius: 6px;
            background: #ffeb00;
            color: #123b70;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .action-btn:hover {
            background: #244f83;
            border-color: #244f83;
            color: white;
        }

        /* CARDS */
        .card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
        }

        .card-header {
            background: #f1f5fa;
            color: #123b70;
        }

        .card h5 {
            color: #123b70;
            font-weight: bold;
        }

        .card h2 {
            font-weight: bold;
        }

        .activity-card {
            transition: 0.3s ease;
        }

        .activity-card:hover {
            border-color: #123b70;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(18, 59, 112, 0.15);
        }

        /* MOBILE */
        @media (max-width: 767px) {

            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 15px;
            }

            .hero-section {
                padding: 25px 20px;
            }

            .hero-section h1 {
                font-size: 24px;
            }

            .action-btn {
                display: block;
                text-align: center;
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVIGATION BAR -->

    <nav class="navbar d-flex align-items-center">

        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">

            <img
                class="barangay-logo"
                src="../logo/barangay ambiong health center logo.jpg"
                alt="Barangay logo"
            >

            Barangay Tawang Health System | BHW

        </a>

        <a href="../logout.php" class="btn logout-btn ms-auto">
            Logout
        </a>

    </nav>

    <div class="container-fluid">

        <div class="row">

            <!-- SIDEBAR -->

            <aside class="col-md-3 col-lg-2 sidebar">

                <h5>BHW Menu</h5>

                <a class="active" href="index.php">Dashboard</a>

                <a href="residents.php">Residents</a>

                <a href="consultations.php">Consultations</a>

                <a href="vaccinations.php">Vaccinations</a>

                <a href="maternal_child.php">Maternal & Child</a>

            </aside>

            <!-- MAIN CONTENT -->

            <main class="col-md-9 col-lg-10 main-content">

                <h2 class="section-title mb-4">
                    BHW Dashboard
                </h2>

                <!-- WELCOME SECTION -->

                <header class="hero-section">

                    <h1>Welcome to BHW Dashboard!</h1>

                    <p>
                        Welcome, <?= h($_SESSION["USERNAME"]) ?>!
                        Manage residents and barangay health services.
                    </p>

                    <a href="residents.php" class="action-btn">
                        View Residents
                    </a>

                    <a href="consultations.php" class="action-btn">
                        Record Consultation
                    </a>

                </header>

                <!-- HEALTH SYSTEM OVERVIEW -->

                <h2 class="section-title">
                    Health System Overview
                </h2>

                <div class="row g-3 mt-2">

                    <?php

                    $cards = [
                        "Total Residents" => $stats["total_residents"] ?? 0,
                        "Consultations" => $stats["total_consultations"] ?? 0,
                        "Vaccinations" => $stats["total_vaccinations"] ?? 0,
                        "Ongoing Pregnancies" => $stats["ongoing_pregnancies"] ?? 0,
                        "Child Health Records" => $stats["child_health_records"] ?? 0,
                        "Pending Appointments" => $stats["pending_appointments"] ?? 0
                    ];

                    foreach ($cards as $title => $number):

                    ?>

                        <div class="col-md-4 col-sm-6">

                            <div class="card shadow-sm h-100">

                                <div class="card-body">

                                    <p class="text-muted">
                                        <?= h($title) ?>
                                    </p>

                                    <h2 class="text-primary">
                                        <?= h($number) ?>
                                    </h2>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <!-- BHW ACTIVITIES -->

                <h2 class="section-title mt-4 mb-3">
                    BHW Activities
                </h2>

                <div class="row g-3">

                    <div class="col-md-6">

                        <a
                            href="residents.php"
                            class="card card-body activity-card text-decoration-none h-100"
                        >

                            <h5>Resident Records</h5>

                            <p class="text-muted mb-0">
                                View registered residents and their information.
                            </p>

                        </a>

                    </div>

                    <div class="col-md-6">

                        <a
                            href="consultations.php"
                            class="card card-body activity-card text-decoration-none h-100"
                        >

                            <h5>Consultations</h5>

                            <p class="text-muted mb-0">
                                Record resident symptoms and treatments.
                            </p>

                        </a>

                    </div>

                    <div class="col-md-6">

                        <a
                            href="vaccinations.php"
                            class="card card-body activity-card text-decoration-none h-100"
                        >

                            <h5>Vaccinations</h5>

                            <p class="text-muted mb-0">
                                Manage vaccination records and schedules.
                            </p>

                        </a>

                    </div>

                    <div class="col-md-6">

                        <a
                            href="maternal_child.php"
                            class="card card-body activity-card text-decoration-none h-100"
                        >

                            <h5>Maternal and Child Health</h5>

                            <p class="text-muted mb-0">
                                Monitor pregnancy and child health records.
                            </p>

                        </a>

                    </div>

                </div>

            </main>

        </div>

    </div>

</body>

</html>