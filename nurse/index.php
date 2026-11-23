<?php
    session_start();

    require_once __DIR__ . "/../HealthSystem.php";

    if(!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'nurse'){
        header("location: ../login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);

    $fullname = $_SESSION['USERNAME'] ?? 'Nurse';

    $stats = $obj->dashboard_stats();
    $low_stock = $obj->show_low_stock();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Nurse Dashboard | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        /* NAVBAR */
        .navbar {
            background-color: #FFEB00;
            border-bottom: 3px solid #123B70;
            padding: 10px 20px;
        }

        .navbar-brand {
            color: #123B70;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
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

        /* SIDEBAR */
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

        /* MAIN CONTENT */
        .main-content {
            padding: 25px;
        }

        .main-content h2,
        .main-content h3 {
            color: #123B70;
        }

        .main-content h2 {
            font-size: 26px;
            font-weight: bold;
        }

        .welcome-header {
            background-color: #123B70;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .welcome-header h2 {
            color: white;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .welcome-header p {
            margin-bottom: 0;
            font-size: 15px;
        }

        .section-title {
            color: #123B70;
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .section-description {
            color: #6c757d;
            margin-bottom: 20px;
        }

        /* CARDS */
        .box {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px #00000012;
        }

        .box:has(.data-table) {
            overflow-x: auto;
        }

        .box h3 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .service-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            height: 100%;
            transition: 0.2s;
        }

        .service-card:hover {
            border-color: #123B70;
        }

        .service-card .card-body {
            padding: 22px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .service-card h5 {
            color: #123B70;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .service-card p {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .service-btn {
            background-color: #123B70;
            color: white;
            padding: 10px 15px;
            margin-top: auto;
            border-radius: 6px;
        }

        .service-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #123B70;
        }

        /* FORM TABLE */
        .form-table {
            width: 100%;
        }

        .form-table th {
            width: 220px;
            text-align: left;
            color: #123B70;
            padding: 8px;
            vertical-align: top;
        }

        .form-table td {
            padding: 8px;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: #123B70;
            color: white;
            padding: 8px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .data-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #f1f4f8;
        }

        .data-table form {
            display: inline-block;
            margin: 0 4px 4px 0;
        }

        .empty {
            text-align: center;
            color: #6c757d;
            padding: 20px;
        }

        .submit-btn {
            background-color: #123B70;
            color: white;
        }

        .submit-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        /* FOOTER */
        .dashboard-footer {
            margin-top: 35px;
            padding: 15px 0;
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #dee2e6;
        }

        /* MOBILE */
        @media(max-width: 767px){

            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 20px 15px;
            }

            .welcome-header {
                padding: 20px;
            }

            .welcome-header h2 {
                font-size: 23px;
            }

            .navbar-brand {
                font-size: 14px;
            }

            .form-table th {
                width: auto;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center gap-2">

        <a href="index.php"
           class="navbar-brand d-flex align-items-center gap-2">

            <img src="../logo/brgylogo.jpg"
                 class="barangay-logo"
                 alt="Barangay Logo">

            <span>Barangay Tawang Health System</span>
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="d-none d-md-inline">
                <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <a href="../logout.php" class="btn logout-btn btn-sm">
                Logout
            </a>

        </div>
    </div>
</nav>

<!-- PAGE LAYOUT -->
<div class="container-fluid">
    <div class="row g-0">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar">

            <h5>Nurse Portal</h5>

            <a href="index.php" class="active">
                Dashboard
            </a>

            <a href="appointments.php">
                Appointments
            </a>

            <a href="consultations.php">
                Consultations
            </a>

            <a href="vaccinations.php">
                Vaccinations
            </a>

            <a href="medicines.php">
                Medicine Inventory
            </a>

        </div>

        <!-- MAIN CONTENT -->
        <main class="col-md-9 col-lg-10 main-content">

            <!-- WELCOME HEADER -->
            <div class="welcome-header">

                <h2>
                    Welcome, <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>!
                </h2>

                <p>
                    Manage appointments, consultations, vaccinations, and medicine stock.
                </p>

            </div>

            <!-- NURSE SERVICES -->
            <div class="mb-4">

                <h3 class="section-title">
                    Nurse Services
                </h3>

                <p class="section-description">
                    Select a service to manage records and requests.
                </p>

                <div class="row g-3">

                    <!-- APPOINTMENTS -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card service-card shadow-sm">
                            <div class="card-body">
                                <h5>Appointments</h5>
                                <p>
                                    Approve or reject appointment requests
                                    from residents.
                                </p>
                                <a href="appointments.php"
                                   class="btn service-btn w-100">
                                    Manage Appointments
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- CONSULTATIONS -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card service-card shadow-sm">
                            <div class="card-body">
                                <h5>Consultations</h5>
                                <p>
                                    Record symptoms, assessments,
                                    and treatments.
                                </p>
                                <a href="consultations.php"
                                   class="btn service-btn w-100">
                                    Manage Consultations
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- VACCINATIONS -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card service-card shadow-sm">
                            <div class="card-body">
                                <h5>Vaccinations</h5>
                                <p>
                                    Record vaccines, doses, next schedules,
                                    and completion status.
                                </p>
                                <a href="vaccinations.php"
                                   class="btn service-btn w-100">
                                    Manage Vaccinations
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- MEDICINES -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card service-card shadow-sm">
                            <div class="card-body">
                                <h5>Medicine Inventory</h5>
                                <p>
                                    Record stock-in, stock-out,
                                    and medicine distribution.
                                </p>
                                <a href="medicines.php"
                                   class="btn service-btn w-100">
                                    Manage Medicines
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SUMMARY -->
            <div class="row g-3 mb-4">

                <div class="col-md-6 col-xl-3">
                    <div class="box mb-0">
                        <h3>Pending Appointments</h3>
                        <div class="stat-number"><?php echo (int)$stats['pending_appointments']; ?></div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="box mb-0">
                        <h3>Total Consultations</h3>
                        <div class="stat-number"><?php echo (int)$stats['total_consultations']; ?></div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="box mb-0">
                        <h3>Vaccination Records</h3>
                        <div class="stat-number"><?php echo (int)$stats['total_vaccinations']; ?></div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="box mb-0">
                        <h3>Low-Stock Medicines</h3>
                        <div class="stat-number"><?php echo (int)$stats['low_stock']; ?></div>
                    </div>
                </div>

            </div>

            <!-- LOW STOCK -->
            <div class="box">

                <h3>Low-Stock Medicines</h3>

                <table class="data-table">

                    <tr>
                        <th>Medicine</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Minimum Stock</th>
                    </tr>

                    <?php if(count($low_stock) > 0): ?>

                        <?php foreach($low_stock as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['medicine_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$row['quantity']; ?></td>
                                <td><?php echo (int)$row['minimum_stock']; ?></td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="empty">All medicines are above minimum stock.</td>
                        </tr>

                    <?php endif; ?>

                </table>

            </div>

            <!-- FOOTER -->
            <div class="dashboard-footer">
                Barangay Tawang Health System | Nurse Portal
            </div>

        </main>

    </div>
</div>

</body>
</html>
