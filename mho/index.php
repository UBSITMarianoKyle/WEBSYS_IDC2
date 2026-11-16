
<?php
session_start();

require_once __DIR__ . "/../HealthSystem.php";

if (!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'mho') {
    header("location: ../login.php");
    exit();
}

$obj = new HealthSystem($pdo);

$stats = $obj->dashboard_stats();

$fullname = $_SESSION['USERNAME'] ?? 'MHO';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MHO Dashboard | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #FFEB00;
            border-bottom: 3px solid #123B70;
            padding: 10px 20px;
        }

        .navbar-brand {
            color: #123B70;
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
            border: 1px solid #123B70;
            color: #123B70;
        }

        .logout-btn:hover {
            background-color: #123B70;
            color: white;
        }

        .sidebar {
            background-color: #123B70;
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
            background-color: #244F83;
        }

        .sidebar a.active {
            background-color: #FFEB00;
            color: #123B70;
            font-weight: bold;
        }

        .main-content {
            padding: 25px;
        }

        .welcome-header {
            background-color: #123B70;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .welcome-header h2 {
            font-size: 26px;
            font-weight: bold;
        }

        .section-title {
            color: #123B70;
            font-size: 23px;
            font-weight: bold;
        }

        .section-description {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 22px;
            height: 100%;
        }

        .stat-card h6 {
            color: #6c757d;
            font-size: 14px;
        }

        .stat-card h3 {
            color: #123B70;
            font-size: 28px;
            font-weight: bold;
        }

        .report-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 22px;
            height: 100%;
        }

        .report-card h5 {
            color: #123B70;
            font-weight: bold;
        }

        .report-card p {
            color: #6c757d;
            font-size: 14px;
        }

        .service-btn {
            background-color: #123B70;
            color: white;
        }

        .service-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        .dashboard-footer {
            margin-top: 35px;
            padding: 15px 0;
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #dee2e6;
        }

        @media(max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center gap-2">

        <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
            <img src="../brgylogo.jpg" class="barangay-logo" alt="Barangay Logo">
            <span>Barangay Tawang Health System</span>
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="d-none d-md-inline">
                <?php echo htmlspecialchars($fullname); ?>
            </span>

            <a href="../logout.php" class="btn logout-btn btn-sm">
                Logout
            </a>
        </div>

    </div>
</nav>

<div class="container-fluid">
    <div class="row g-0">

        <div class="col-md-3 col-lg-2 sidebar">

            <h5>MHO Portal</h5>

            <a href="index.php" class="active">
                Dashboard
            </a>

            <a href="reports.php">
                Health Reports
            </a>

        </div>

        <main class="col-md-9 col-lg-10 main-content">

            <div class="welcome-header">
                <h2>
                    Welcome, <?php echo htmlspecialchars($fullname); ?>!
                </h2>

                <p class="mb-0">
                    Monitor the overall health services and activities
                    of Barangay Tawang.
                </p>
            </div>

            <h3 class="section-title">Health Overview</h3>

            <p class="section-description">
                Summary of the barangay's health records and services.
            </p>

            <div class="row g-3">

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Total Residents</h6>
                        <h3><?php echo $stats['total_residents']; ?></h3>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Total Consultations</h6>
                        <h3><?php echo $stats['total_consultations']; ?></h3>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Total Vaccinations</h6>
                        <h3><?php echo $stats['total_vaccinations']; ?></h3>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Ongoing Pregnancies</h6>
                        <h3><?php echo $stats['ongoing_pregnancies']; ?></h3>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Child Health Records</h6>
                        <h3><?php echo $stats['child_health_records']; ?></h3>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Pending Appointments</h6>
                        <h3><?php echo $stats['pending_appointments']; ?></h3>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <h6>Low Stock Medicines</h6>
                        <h3><?php echo $stats['low_stock']; ?></h3>
                    </div>
                </div>

            </div>

            <div class="mt-4">

                <h3 class="section-title">Health Management</h3>

                <p class="section-description">
                    View the health reports and records of the barangay.
                </p>

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="report-card">
                            <h5>Health Reports</h5>

                            <p>
                                View consultations, vaccinations,
                                maternal and child health records,
                                and other health activities.
                            </p>

                            <a href="reports.php" class="btn service-btn">
                                View Reports
                            </a>
                        </div>
                    </div>

                </div>

            </div>

            <div class="dashboard-footer">
                Barangay Tawang Health System | MHO Portal
            </div>

        </main>

    </div>
</div>

</body>
</html>
