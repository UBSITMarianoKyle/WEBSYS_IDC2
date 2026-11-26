<?php
session_start();
require_once __DIR__ . '/../HealthSystem.php';

if (!isset($_SESSION['ID']) || !isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$obj = new HealthSystem($pdo);
$fullname = 'Administrator';
if (isset($_SESSION['USERNAME'])) {
    $fullname = $_SESSION['USERNAME'];
}

// Report filters
$year = (int)date('Y');
if (isset($_GET['year'])) { $year = (int)$_GET['year']; }
$month = 0;
if (isset($_GET['month'])) { $month = (int)$_GET['month']; }

if ($year < 1900 || $year > (int)date('Y')) {
    $year = (int)date('Y');
}

if ($month < 0 || $month > 12) {
    $month = 0;
}

// Report period
 $firstMonth = 1;
if ($month > 0) { $firstMonth = $month; }
$start = sprintf('%04d-%02d-01', $year, $firstMonth);

if ($month == 0) {
    $end = ($year + 1) . '-01-01';
    $period = 'January - December ' . $year;
} else {
    $end = date('Y-m-d', strtotime($start . ' +1 month'));
    $period = date('F Y', strtotime($start));
}

// Resident statistics
$residentCount = $obj->report_residentcount();

$maleCount = $obj->report_malecount();

$femaleCount = $obj->report_femalecount();

$otherCount = $residentCount - $maleCount - $femaleCount;

$householdCount = $obj->report_householdcount();

// Consultation report


$consultations = [];
for ($i = 1; $i <= 12; $i++) { $consultations[$i] = 0; }

foreach ($obj->report_consultations_period_2($start, $end) as $row) {
    $consultations[$row['month_no']] = $row['total'];
}

$consultationCount = 0;
foreach ($consultations as $total) { $consultationCount += $total; }

// Vaccination report

$vaccinations = $obj->report_vaccinations_period($start, $end);

// Overall vaccination coverage
$vaccinatedCount = $obj->report_vaccinatedcount();

$coverage = 0;

if ($residentCount > 0) {
    $coverage = round(
        ($vaccinatedCount / $residentCount) * 100, 1
    );
}

// Medicine distribution report

$medicineReport = $obj->report_medicine_transactions_period_2($start, $end);

// Current medicine inventory
$medicines = $obj->report_medicines_2();

// Pregnancy registrations

$pregnancyCount = $obj->report_pregnancies_period($start, $end);

// Prenatal visits

$prenatalCount = $obj->report_prenatal_visits_period($start, $end);

// Child health records

$childCount = $obj->report_child_health_period($start, $end);

// Scheduled health visits

$healthVisitCount = $obj->report_health_visits_period($start, $end);

// Appointment report


$appointments = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'completed' => 0
];

foreach ($obj->report_appointments_period($start, $end) as $row) {
    $appointments[$row['status']] = $row['total'];
}

$appointmentTotal = 0;
foreach ($appointments as $total) { $appointmentTotal += $total; }
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Health Reports | Barangay Tawang Health System</title>

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
            background: #123B70;
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
            font-weight: bold;
        }

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
            margin-bottom: 18px;
        }

        /* STATISTICS */

        .stat-number {
            color: #123B70;
            font-size: 30px;
            font-weight: bold;
        }

        .stat-card {
            background: #f5f7fa;
            border-radius: 8px;
            padding: 20px;
            height: 100%;
        }

        .progress {
            height: 16px;
            margin-top: 8px;
        }

        .progress-bar {
            background-color: #123B70;
        }

        /* TABLES */

        .table thead th {
            background-color: #123B70;
            color: white;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        /* BUTTONS */

        .btn-main {
            background: #123B70;
            color: white;
        }

        .btn-main:hover {
            background: #244F83;
            color: white;
        }

        .btn-print {
            background: #FFEB00;
            color: #123B70;
            border: 1px solid #123B70;
        }

        /* MOBILE */

        @media(max-width: 767px) {

            .main-content {
                padding: 15px;
            }

            .card-box {
                padding: 15px;
            }

            .navbar-brand {
                font-size: 14px;
            }

            .sidebar {
                min-height: auto;
            }

            .stat-number {
                font-size: 24px;
            }
        }

        /* PRINT DESIGN */

        @media print {

            .navbar,
            .sidebar,
            .no-print,
            .mobile-menu-toggle {
                display: none !important;
            }

            body {
                background: white !important;
                color: black !important;
            }

            .main-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-box {
                box-shadow: none;
                border: 1px solid #ccc;
            }

            .stat-card {
                background: white;
                border: 1px solid #ccc;
            }

            .table thead th {
                background: white !important;
                color: black !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            tr {
                break-inside: avoid;
            }

            h5 {
                break-after: avoid;
            }

            .print-header {
                display: block !important;
            }

        }

        .print-header {
            display: none;
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
                justify-content-between align-items-center gap-2">

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

            <a href="index.php">Dashboard</a>

            <a href="users.php">Users</a>

            <a href="residents.php">Residents</a>

            <a href="households.php">Households</a>

            <a href="medicines.php">Medicines</a>

            <a href="reports.php" class="active">Reports</a>

            <a href="patient_records.php">Patient Records</a>

        </div>


        <!-- MAIN CONTENT -->

        <main class="col-md-9 col-lg-10 main-content">

            <!-- PRINT HEADER -->

            <div class="print-header text-center mb-4">

                <img src="../brgylogo.png"
                     width="80"
                     alt="Barangay Logo">

                <h5 class="mt-2">Republic of the Philippines</h5>

                <p class="mb-0">Province of Benguet</p>

                <p class="mb-0">Municipality of La Trinidad</p>

                <h4>Barangay Tawang</h4>

                <hr>

            </div>


            <!-- PAGE HEADER -->

            <h2 class="section-title">
                Detailed Health Reports
            </h2>

            <p class="text-muted">
                Barangay Health Services and Community Health Monitoring
            </p>


            <!-- REPORT FILTER -->

            <div class="card-box no-print">

                <h5>Report Filters</h5>

                <form method="GET">

                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">

                            <label class="form-label">Year</label>

                            <input type="number"
                                   name="year"
                                   class="form-control"
                                   min="1900"
                                   max="<?php echo  date('Y') ?>"
                                   value="<?php echo  $year ?>"
                                   required>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label">Month</label>

                            <select name="month" class="form-select">

                                <option value="0">
                                    All Months
                                </option>

                                <?php for ($i = 1; $i <= 12; $i++): ?>

                                    <option value="<?php echo  $i ?>"
                                        <?php if ($month == $i) { echo 'selected'; } else { echo ''; } ?>>

                                        <?php echo  date('F', mktime(0, 0, 0, $i, 1)) ?>

                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <button type="submit"
                                    class="btn btn-main w-100">

                                Generate Report

                            </button>

                        </div>

                        <div class="col-md-3">

                            <button type="button"
                                    class="btn btn-print w-100"
                                    onclick="window.print()">

                                Print Report

                            </button>

                        </div>

                    </div>

                </form>

            </div>


            <div class="mb-4">

                <strong>Reporting Period:</strong>
                <?php echo  htmlspecialchars($period) ?>

                <br>

                <strong>Date Generated:</strong>
                <?php echo  date('F d, Y') ?>

            </div>


            <!-- RESIDENT REGISTRATION REPORT -->

            <div class="card-box">

                <h5>1. Resident Health Registration</h5>

                <p class="text-muted small">
                    Current registered population and households.
                    These totals are not filtered by reporting period.
                </p>

                <div class="row g-3 mb-4">

                    <div class="col-6 col-md-3">

                        <div class="stat-card">

                            <small>Total Residents</small>

                            <div class="stat-number">
                                <?php echo  $residentCount ?>
                            </div>

                        </div>

                    </div>

                    <div class="col-6 col-md-3">

                        <div class="stat-card">

                            <small>Male Residents</small>

                            <div class="stat-number">
                                <?php echo  $maleCount ?>
                            </div>

                        </div>

                    </div>

                    <div class="col-6 col-md-3">

                        <div class="stat-card">

                            <small>Female Residents</small>

                            <div class="stat-number">
                                <?php echo  $femaleCount ?>
                            </div>

                        </div>

                    </div>

                    <div class="col-6 col-md-3">

                        <div class="stat-card">

                            <small>Registered Households</small>

                            <div class="stat-number">
                                <?php echo  $householdCount ?>
                            </div>

                        </div>

                    </div>

                </div>

                <?php
                    $population = [
                        'Male' => $maleCount,
                        'Female' => $femaleCount,
                        'Other / Unspecified' => $otherCount
                    ];
                ?>

                <?php foreach ($population as $label => $total): ?>

                    <?php
                        $percent = round(
                            ($total / max(1, $residentCount)) * 100, 1
                        );
                    ?>

                    <div class="d-flex justify-content-between mt-3">

                        <span><?php echo  $label ?></span>

                        <span><?php echo  $total ?> (<?php echo  $percent ?>%)</span>

                    </div>

                    <div class="progress">

                        <div class="progress-bar"
                             style="width: <?php echo  $percent ?>%">

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <!-- CONSULTATION REPORT -->

            <div class="card-box">

                <h5>2. Patient Consultation Report</h5>

                <p>
                    Total Consultations:
                    <strong><?php echo  $consultationCount ?></strong>
                </p>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>
                                <th>Month</th>
                                <th>Consultations</th>
                                <th>Percentage</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($consultations as $number => $total): ?>

                                <?php if ($month == 0 || $month == $number): ?>

                                    <?php
                                        $percent = round(
                                            ($total /
                                            max(1, $consultationCount)) * 100,
                                            1
                                        );
                                    ?>

                                    <tr>

                                        <td>
                                            <?php echo  date(
                                                'F',
                                                mktime(0, 0, 0, $number, 1)
                                            ) ?>
                                        </td>

                                        <td><?php echo  $total ?></td>

                                        <td><?php echo  $percent ?>%</td>

                                    </tr>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </tbody>

                        <tfoot>

                            <tr class="fw-bold">

                                <td>Total</td>

                                <td><?php echo  $consultationCount ?></td>

                                <td>
                                    <?php if ($consultationCount > 0) { echo '100%'; } else { echo '0%'; } ?>
                                </td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>


            <!-- VACCINATION REPORT -->

            <div class="card-box">

                <h5>3. Vaccination Monitoring Report</h5>

                <div class="stat-card mb-3">

                    <small>Overall Vaccination Coverage</small>

                    <div class="stat-number">

                        <?php if ($residentCount > 0) { echo $coverage . '%'; } else { echo 'N/A'; } ?>

                    </div>

                    <div class="progress">

                        <div class="progress-bar"
                             style="width: <?php echo  $coverage ?>%">

                        </div>

                    </div>

                    <p class="small mt-2 mb-0">

                        <?php echo  $vaccinatedCount ?> out of
                        <?php echo  $residentCount ?> registered residents
                        have at least one completed vaccination record.

                    </p>

                    <p class="text-muted small mb-0">
                        All-time coverage; does not indicate full immunization.
                    </p>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th>Vaccine Type</th>
                                <th>Total Records</th>
                                <th>Completed</th>
                                <th>Not Completed</th>
                                <th>Completion Rate</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($vaccinations as $row): ?>

                                <?php
                                    $rate = round(
                                        ($row['completed'] /
                                        max(1, $row['total'])) * 100,
                                        1
                                    );
                                ?>

                                <tr>

                                    <td>
                                        <?php echo  htmlspecialchars((string)$row['vaccine_name']) ?>
                                    </td>

                                    <td><?php echo  $row['total'] ?></td>

                                    <td><?php echo  $row['completed'] ?></td>

                                    <td>
                                        <?php echo  $row['total'] - $row['completed'] ?>
                                    </td>

                                    <td><?php echo  $rate ?>%</td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (empty($vaccinations)): ?>

                                <tr>

                                    <td colspan="5" class="text-center">
                                        No vaccination records for this period.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- MATERNAL AND CHILD HEALTH REPORT -->

            <div class="card-box">

                <h5>4. Maternal and Child Health Report</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th>Health Service</th>
                                <th>Total Records</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>Pregnancy Registrations</td>
                                <td><?php echo  $pregnancyCount ?></td>

                            </tr>

                            <tr>

                                <td>Prenatal Visits</td>
                                <td><?php echo  $prenatalCount ?></td>

                            </tr>

                            <tr>

                                <td>Child Health Records</td>
                                <td><?php echo  $childCount ?></td>

                            </tr>

                            <tr>

                                <td>Scheduled Health Visits</td>
                                <td><?php echo  $healthVisitCount ?></td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- MEDICINE DISTRIBUTION REPORT -->

            <div class="card-box">

                <h5>5. Medicine Distribution Report</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th>Medicine Name</th>
                                <th>Unit</th>
                                <th>Stock-In</th>
                                <th>Stock-Out</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($medicineReport as $row): ?>

                                <tr>

                                    <td>
                                        <?php echo  htmlspecialchars($row['medicine_name']) ?>
                                    </td>

                                    <td>
                                        <?php echo  htmlspecialchars($row['unit']) ?>
                                    </td>

                                    <td><?php echo  $row['stock_in'] ?></td>

                                    <td><?php echo  $row['stock_out'] ?></td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (empty($medicineReport)): ?>

                                <tr>

                                    <td colspan="4" class="text-center">
                                        No medicine transactions for this period.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- CURRENT MEDICINE INVENTORY -->

            <div class="card-box">

                <h5>6. Current Medicine Inventory</h5>

                <p class="text-muted small">
                    Current stock levels, regardless of the selected period.
                </p>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th>Medicine</th>
                                <th>Unit</th>
                                <th>Available Stock</th>
                                <th>Minimum Stock</th>
                                <th>Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($medicines as $row): ?>

                                <tr>

                                    <td>
                                        <?php echo  htmlspecialchars($row['medicine_name']) ?>
                                    </td>

                                    <td>
                                        <?php echo  htmlspecialchars($row['unit']) ?>
                                    </td>

                                    <td><?php echo  $row['quantity'] ?></td>

                                    <td><?php echo  $row['minimum_stock'] ?></td>

                                    <td>

                                        <?php if ($row['quantity'] == 0): ?>

                                            <span class="text-danger fw-bold">
                                                Out of Stock
                                            </span>

                                        <?php elseif ($row['quantity'] <=
                                                      $row['minimum_stock']): ?>

                                            <span class="text-danger fw-bold">
                                                Low Stock
                                            </span>

                                        <?php else: ?>

                                            <span class="text-success fw-bold">
                                                In Stock
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (empty($medicines)): ?>

                                <tr>

                                    <td colspan="5" class="text-center">
                                        No medicines available.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- APPOINTMENT REPORT -->

            <div class="card-box">

                <h5>7. Appointment Management Report</h5>

                <p>
                    Total Appointments:
                    <strong><?php echo  $appointmentTotal ?></strong>
                </p>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th>Appointment Status</th>
                                <th>Total</th>
                                <th>Percentage</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($appointments as $status => $total): ?>

                                <?php
                                    $percent = round(
                                        ($total /
                                        max(1, $appointmentTotal)) * 100,
                                        1
                                    );
                                ?>

                                <tr>

                                    <td>
                                        <?php echo  htmlspecialchars(ucfirst($status)) ?>
                                    </td>

                                    <td><?php echo  $total ?></td>

                                    <td><?php echo  $percent ?>%</td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- REPORT SIGNATURE -->

            <div class="card-box">

                <p>Prepared by:</p>

                <br>

                <strong><?php echo  htmlspecialchars($fullname) ?></strong>

                <p class="mb-0">System Administrator</p>

                <p>Barangay Tawang Health System</p>

            </div>


            <a href="index.php"
               class="btn btn-main no-print">

                Back to Dashboard

            </a>

        </main>

    </div>

</div>

</body>
</html>
