<?php
    session_start();
    require_once __DIR__ . '/../HealthSystem.php';

    // Admin access only
    if (!isset($_SESSION['ID']) || !isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 'admin') {
        header('Location: ../login.php');
        exit();
    }

    $obj = new HealthSystem($pdo);
    $fullname = 'Administrator';
if (isset($_SESSION['USERNAME'])) {
    $fullname = $_SESSION['USERNAME'];
}

    $year = (int)date('Y');

    // Registered residents
    $residentCount = $obj->report_residentcount();

    // Registered users
    $userCount = $obj->report_usercount();

    // Medicine inventory
    $medicineCount = $obj->report_medicinecount();

    // Low-stock supplies
    $lowStockCount = $obj->report_lowstockcount();

    // Monthly consultations
    

    $monthly = [];
for ($i = 1; $i <= 12; $i++) { $monthly[$i] = 0; }

    foreach ($obj->report_consultations_period($year) as $row) {
        $monthly[(int)$row['month_no']] = (int)$row['total'];
    }

    $annualTotal = 0;
foreach ($monthly as $total) { $annualTotal += $total; }
// Vaccination statistics
    $vaccinated = $obj->report_vaccinated();

    $coverage = 0;

    if ($residentCount > 0) {
        $coverage = round(($vaccinated / $residentCount) * 100, 1);
    }

    // Appointment statistics
    $appointments = $obj->report_appointments();

    $appointmentTotal = 0;
foreach ($appointments as $row) { $appointmentTotal += $row['total']; }

    // Medicine stock monitoring
    $medicines = $obj->report_medicines();

    // Medicine distribution
    

    $distribution = $obj->report_medicine_transactions_period($year);

    // Maternal and child health statistics
    $maternal = [];


    $maternal['Pregnancy Registrations'] = $obj->report_pregnancies_count();
$maternal['Prenatal Visits'] = $obj->report_prenatal_visits_count();
$maternal['Child Health Records'] = $obj->report_child_health_count();
$maternal['Scheduled Health Visits'] = $obj->report_health_visits_count();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Admin Dashboard | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        /* GENERAL DESIGN */

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

        .section-title {
            color: #123B70;
            font-size: 25px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .section-description {
            color: #6c757d;
            margin-bottom: 20px;
        }

        /* STATISTICS CARDS */

        .card-box {
            background: white;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px #00000012;
        }

        .card-box h5 {
            color: #123B70;
            font-weight: bold;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #123B70;
        }

        /* MONTHLY CONSULTATION CHART */

        .chart-scroll {
            overflow-x: auto;
        }

        .month-chart {
            display: flex;
            gap: 8px;
            min-width: 480px;
            align-items: end;
        }

        .month-column {
            flex: 1;
            min-width: 0;
            text-align: center;
            font-size: 12px;
        }

        .month-track {
            height: 160px;
            display: flex;
            align-items: end;
            justify-content: center;
            border-bottom: 1px solid #ddd;
        }

        .month-bar {
            background: #123B70;
            width: 65%;
            border-radius: 4px 4px 0 0;
        }

        /* VACCINATION DOUGHNUT CHART */

        .coverage-ring {
            width: 150px;
            height: 150px;
            margin: 20px auto;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: conic-gradient(
                #123B70 var(--coverage),
                #e9ecef 0
            );
        }

        .coverage-ring strong {
            display: grid;
            place-items: center;
            background: white;
            border-radius: 50%;
            width: 116px;
            height: 116px;
            color: #123B70;
            font-size: 26px;
        }

        /* PROGRESS BARS */

        .progress {
            height: 14px;
            margin-top: 6px;
            background-color: #e9ecef;
        }

        .progress-bar {
            background-color: #123B70;
        }

        /* TABLE */

        .table thead th {
            background: #123B70;
            color: white;
        }

        /* BUTTON */

        .btn-main {
            background: #123B70;
            color: white;
        }

        .btn-main:hover {
            background: #244F83;
            color: white;
        }

        /* MOBILE RESPONSIVENESS */

        @media(max-width: 767px) {

            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 16px;
            }

            .navbar-brand {
                font-size: 14px;
            }

            .section-title {
                font-size: 22px;
            }

            .card-box {
                padding: 16px;
            }

            .stat-number {
                font-size: 26px;
            }

            .card-box h5 {
                font-size: 17px;
            }
        }

    </style>

    <link rel="stylesheet" href="../mobile_additions.css">

</head>

<body>

    <!-- NAVIGATION BAR -->

    <nav class="navbar">

        <button type="button"
                class="mobile-menu-toggle"
                aria-label="Toggle menu"
                aria-expanded="false"
                onclick="document.body.classList.toggle('menu-open');
                this.setAttribute('aria-expanded',
                document.body.classList.contains('menu-open'))">

            ☰

        </button>

        <div class="container-fluid d-flex
                    justify-content-between
                    align-items-center gap-2">

            <a href="index.php"
               class="navbar-brand d-flex align-items-center gap-2">

                <img src="../brgylogo.png"
                     class="barangay-logo"
                     alt="Barangay Logo">

                <span>Barangay Tawang Health System</span>

            </a>

            <div class="d-flex align-items-center gap-3">

                <span class="d-none d-md-inline">
                    <?php echo  htmlspecialchars($fullname) ?>
                </span>

                <a href="../logout.php"
                   class="btn logout-btn btn-sm">

                    Logout

                </a>

            </div>

        </div>

    </nav>


    <div class="container-fluid">

        <div class="row g-0">

            <!-- SIDEBAR -->

            <div class="col-md-3 col-lg-2 sidebar">

                <h5>Admin Portal</h5>

                <a href="index.php" class="active">
                    Dashboard
                </a>

                <a href="users.php">
                    Users
                </a>

                <a href="residents.php">
                    Residents
                </a>

                <a href="households.php">
                    Households
                </a>

                <a href="medicines.php">
                    Medicines
                </a>

                <a href="reports.php">
                    Reports
                </a>

                <a href="patient_records.php">
                    Patient Records
                </a>

            </div>


            <!-- MAIN CONTENT -->

            <main class="col-md-9 col-lg-10 main-content">

                <div class="page-header mb-4">

                    <h2 class="section-title">
                        Admin Dashboard
                    </h2>

                    <p class="section-description">
                        Community Health Statistics and Analytics |
                        <?php echo  $year ?>
                        <br>
                        Welcome, <?php echo  htmlspecialchars($fullname) ?>
                    </p>

                </div>


                <!-- SUMMARY STATISTICS -->

                <div class="row g-3 mb-3">

                    <div class="col-6 col-xl-3">

                        <div class="card-box h-100 mb-0">

                            <small>Registered Residents</small>

                            <div class="stat-number">
                                <?php echo  number_format($residentCount) ?>
                            </div>

                        </div>

                    </div>


                    <div class="col-6 col-xl-3">

                        <div class="card-box h-100 mb-0">

                            <small>Registered Users</small>

                            <div class="stat-number">
                                <?php echo  number_format($userCount) ?>
                            </div>

                        </div>

                    </div>


                    <div class="col-6 col-xl-3">

                        <div class="card-box h-100 mb-0">

                            <small>Consultations This Year</small>

                            <div class="stat-number">
                                <?php echo  number_format($annualTotal) ?>
                            </div>

                        </div>

                    </div>


                    <div class="col-6 col-xl-3">

                        <div class="card-box h-100 mb-0">

                            <small>Low-Stock Supplies</small>

                            <div class="stat-number text-danger">
                                <?php echo  number_format($lowStockCount) ?>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- MONTHLY CONSULTATION BAR CHART -->

                <div class="card-box">

                    <h5>
                        Monthly Consultations (<?php echo  $year ?>)
                    </h5>

                    <p class="text-muted small">
                        Number of patient consultations recorded each month.
                    </p>

                    <div class="chart-scroll">

                        <div class="month-chart">

                            <?php foreach ($monthly as $month => $total): ?>

                                <?php
                                    $height = ($total /
                                        max(1, max($monthly))) * 100;
                                ?>

                                <div class="month-column">

                                    <span>
                                        <?php echo  $total ?>
                                    </span>

                                    <div class="month-track">

                                        <div class="month-bar"
                                             style="height: <?php echo  $height ?>%">
                                        </div>

                                    </div>

                                    <small>

                                        <?php echo  date(
                                            'M',
                                            mktime(0, 0, 0, $month, 1)
                                        ) ?>

                                    </small>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                </div>


                <div class="row g-3 mb-3">

                    <!-- VACCINATION COVERAGE -->

                    <div class="col-lg-6">

                        <div class="card-box h-100 mb-0">

                            <h5>Vaccination Coverage</h5>

                            <div class="coverage-ring"
                                 style="--coverage: <?php echo  $coverage ?>%">

                                <strong>

                                    <?php if ($residentCount > 0): ?>

                                        <?php echo  $coverage ?>%

                                    <?php else: ?>

                                        N/A

                                    <?php endif; ?>

                                </strong>

                            </div>

                            <p class="text-center small mb-0">

                                <?php echo  $vaccinated ?> out of
                                <?php echo  $residentCount ?> registered residents
                                have at least one completed vaccination record.

                            </p>

                            <p class="text-center text-muted small mt-2 mb-0">
                                This does not indicate full immunization.
                            </p>

                        </div>

                    </div>


                    <!-- APPOINTMENT STATISTICS -->

                    <div class="col-lg-6">

                        <div class="card-box h-100 mb-0">

                            <h5>Appointment Status</h5>

                            <p class="text-muted small">
                                Total Appointments: <?php echo  $appointmentTotal ?>
                            </p>

                            <?php if (!$appointments): ?>

                                <p>No appointments recorded.</p>

                            <?php endif; ?>

                            <?php foreach ($appointments as $row): ?>

                                <?php

                                    $percent = (
                                        (int)$row['total'] /
                                        max(1, $appointmentTotal)
                                    ) * 100;

                                    $color = '#123B70';

                                    if (strtolower($row['status']) == 'pending') {
                                        $color = '#E5C900';
                                    }

                                    if (strtolower($row['status']) == 'rejected') {
                                        $color = '#dc3545';
                                    }

                                    if (strtolower($row['status']) == 'completed') {
                                        $color = '#198754';
                                    }

                                ?>

                                <div class="d-flex
                                            justify-content-between mt-3">

                                    <span>

                                        <?php echo  htmlspecialchars(
                                            ucfirst($row['status'])
                                        ) ?>

                                    </span>

                                    <span>

                                        <?php echo  (int)$row['total'] ?>

                                        (<?php echo  round($percent, 1) ?>%)

                                    </span>

                                </div>

                                <div class="progress">

                                    <div class="progress-bar"
                                         style="width: <?php echo  $percent ?>%;
                                         background-color: <?php echo  $color ?>">

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                </div>


                <!-- MEDICINE STOCK MONITORING -->

                <div class="card-box">

                    <h5>Medicine Stock Monitoring</h5>

                    <p class="text-muted small">

                        Displays up to 10 medicine records with the lowest stock.

                        Red indicates low stock.

                    </p>

                    <?php if (!$medicines): ?>

                        <p>No medicine records available.</p>

                    <?php endif; ?>

                    <?php foreach ($medicines as $row): ?>

                        <?php

                            $lowStock = $row['quantity'] <=
                                        $row['minimum_stock'];

                            $percent = (
                                $row['quantity'] /
                                max(
                                    1,
                                    $row['quantity'],
                                    $row['minimum_stock']
                                )
                            ) * 100;

                        ?>

                        <div class="d-flex
                                    justify-content-between
                                    flex-wrap mt-3">

                            <span>

                                <?php echo  htmlspecialchars($row['medicine_name']) ?>

                                <?php if ($lowStock): ?>

                                    <span class="text-danger small">
                                        (Low Stock)
                                    </span>

                                <?php endif; ?>

                            </span>

                            <span>

                                <?php echo  (int)$row['quantity'] ?>

                                <?php echo  htmlspecialchars($row['unit']) ?>

                                | Minimum: <?php echo  (int)$row['minimum_stock'] ?>

                            </span>

                        </div>

                        <div class="progress">

                            <div class="progress-bar"
                                 style="width: <?php echo  $percent ?>%;
                                 background-color:
                                 <?php if ($lowStock) { echo '#dc3545'; } else { echo '#123B70'; } ?>">

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- MEDICINE DISTRIBUTION REPORT -->

                <div class="card-box">

                    <h5>
                        Medicine Distribution (<?php echo  $year ?>)
                    </h5>

                    <p class="text-muted small">
                        Total quantity of medicines distributed this year.
                    </p>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>

                                <tr>

                                    <th>Medicine</th>
                                    <th>Unit</th>
                                    <th>Quantity Distributed</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($distribution as $row): ?>

                                    <tr>

                                        <td>
                                            <?php echo  htmlspecialchars($row['medicine_name']) ?>
                                        </td>

                                        <td>
                                            <?php echo  htmlspecialchars($row['unit']) ?>
                                        </td>

                                        <td>
                                            <?php echo  (int)$row['total'] ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                <?php if (!$distribution): ?>

                                    <tr>

                                        <td colspan="3" class="text-center">

                                            No medicine distribution records
                                            this year.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- MATERNAL AND CHILD HEALTH STATISTICS -->

                <div class="card-box">

                    <h5>Maternal and Child Health Statistics</h5>

                    <p class="text-muted small">
                        Total recorded maternal and child health services.
                    </p>

                    <?php foreach ($maternal as $label => $total): ?>

                        <?php

                            $percent = (
                                $total /
                                max(1, max($maternal))
                            ) * 100;

                        ?>

                        <div class="d-flex
                                    justify-content-between mt-3">

                            <span>
                                <?php echo  $label ?>
                            </span>

                            <strong>
                                <?php echo  $total ?>
                            </strong>

                        </div>

                        <div class="progress">

                            <div class="progress-bar"
                                 style="width: <?php echo  $percent ?>%">

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- REPORTS BUTTON -->

                <a href="reports.php" class="btn btn-main">

                    View Detailed Health Reports

                </a>


            </main>

        </div>

    </div>

</body>

</html>
