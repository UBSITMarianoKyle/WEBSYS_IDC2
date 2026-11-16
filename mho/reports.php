
<?php
session_start();

require_once __DIR__ . "/../HealthSystem.php";

if (!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'mho') {
    header("location: ../login.php");
    exit();
}

$obj = new HealthSystem($pdo);

$consultations = $obj->show_consultations();
$vaccinations = $obj->show_vaccinations();
$pregnancies = $obj->show_pregnancies();
$prenatal = $obj->show_prenatal_visits();
$children = $obj->show_child_health();
$healthVisits = $obj->show_health_visits();
$medicines = $obj->show_low_stock();

$fullname = $_SESSION['USERNAME'] ?? 'MHO';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Health Reports | Barangay Tawang Health System</title>

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

        .section-title {
            color: #123B70;
            font-size: 23px;
            font-weight: bold;
        }

        .report-section {
            background-color: white;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .report-section h5 {
            color: #123B70;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .table {
            font-size: 14px;
            vertical-align: middle;
        }

        .table thead {
            background-color: #123B70;
            color: white;
        }

        .dashboard-footer {
            margin-top: 30px;
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
                <?php echo e($fullname); ?>
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

            <a href="index.php">
                Dashboard
            </a>

            <a href="reports.php" class="active">
                Health Reports
            </a>

        </div>

        <main class="col-md-9 col-lg-10 main-content">

            <div class="welcome-header">
                <h2>Health Reports</h2>

                <p class="mb-0">
                    View and monitor the health records and activities
                    of Barangay Tawang.
                </p>
            </div>

            <!-- CONSULTATION REPORTS -->

            <div class="report-section">

                <h5>Consultation Reports</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Date</th>
                                <th>Symptoms</th>
                                <th>Assessment</th>
                                <th>Treatment</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($consultations): ?>

                                <?php foreach ($consultations as $row): ?>

                                    <tr>
                                        <td>
                                            <?php echo e($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </td>

                                        <td><?php echo e($row['consultation_date']); ?></td>
                                        <td><?php echo e($row['symptoms']); ?></td>
                                        <td><?php echo e($row['assessment']); ?></td>
                                        <td><?php echo e($row['treatment']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center">
                                        No consultation records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- VACCINATION REPORTS -->

            <div class="report-section">

                <h5>Vaccination Reports</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Vaccine</th>
                                <th>Dose Number</th>
                                <th>Vaccination Date</th>
                                <th>Next Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($vaccinations): ?>

                                <?php foreach ($vaccinations as $row): ?>

                                    <tr>
                                        <td>
                                            <?php echo e($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </td>

                                        <td><?php echo e($row['vaccine_name']); ?></td>
                                        <td><?php echo e($row['dose_number']); ?></td>
                                        <td><?php echo e($row['vaccination_date']); ?></td>
                                        <td><?php echo e($row['next_schedule']); ?></td>
                                        <td><?php echo e($row['status']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center">
                                        No vaccination records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- MATERNAL HEALTH REPORTS -->

            <div class="report-section">

                <h5>Maternal Health Reports</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Registration Date</th>
                                <th>Expected Delivery</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($pregnancies): ?>

                                <?php foreach ($pregnancies as $row): ?>

                                    <tr>
                                        <td>
                                            <?php echo e($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </td>

                                        <td><?php echo e($row['registration_date']); ?></td>
                                        <td><?php echo e($row['expected_delivery']); ?></td>
                                        <td><?php echo e($row['status']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="4" class="text-center">
                                        No maternal health records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- PRENATAL VISIT REPORTS -->

            <div class="report-section">

                <h5>Prenatal Visit Reports</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Visit Date</th>
                                <th>Blood Pressure</th>
                                <th>Weight</th>
                                <th>Notes</th>
                                <th>Next Visit</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($prenatal): ?>

                                <?php foreach ($prenatal as $row): ?>

                                    <tr>
                                        <td>
                                            <?php echo e($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </td>

                                        <td><?php echo e($row['visit_date']); ?></td>
                                        <td><?php echo e($row['blood_pressure']); ?></td>
                                        <td><?php echo e($row['weight']); ?></td>
                                        <td><?php echo e($row['notes']); ?></td>
                                        <td><?php echo e($row['next_visit']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center">
                                        No prenatal visit records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- CHILD HEALTH REPORTS -->

            <div class="report-section">

                <h5>Child Health Reports</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Visit Date</th>
                                <th>Height</th>
                                <th>Weight</th>
                                <th>Notes</th>
                                <th>Next Visit</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($children): ?>

                                <?php foreach ($children as $row): ?>

                                    <tr>
                                        <td>
                                            <?php echo e($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </td>

                                        <td><?php echo e($row['visit_date']); ?></td>
                                        <td><?php echo e($row['height']); ?></td>
                                        <td><?php echo e($row['weight']); ?></td>
                                        <td><?php echo e($row['notes']); ?></td>
                                        <td><?php echo e($row['next_visit']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center">
                                        No child health records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- HEALTH VISIT REPORTS -->

            <div class="report-section">

                <h5>Health Visit Reports</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Visit Type</th>
                                <th>Scheduled Date</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($healthVisits): ?>

                                <?php foreach ($healthVisits as $row): ?>

                                    <tr>
                                        <td>
                                            <?php echo e($row['first_name'] . ' ' . $row['last_name']); ?>
                                        </td>

                                        <td><?php echo e($row['visit_type']); ?></td>
                                        <td><?php echo e($row['scheduled_date']); ?></td>
                                        <td><?php echo e($row['status']); ?></td>
                                        <td><?php echo e($row['notes']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center">
                                        No health visit records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>

            <!-- LOW STOCK MEDICINES -->

            <div class="report-section">

                <h5>Low Stock Medicines</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Unit</th>
                                <th>Available Quantity</th>
                                <th>Minimum Stock</th>
                                <th>Expiration Date</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($medicines): ?>

                                <?php foreach ($medicines as $row): ?>

                                    <tr>
                                        <td><?php echo e($row['medicine_name']); ?></td>
                                        <td><?php echo e($row['unit']); ?></td>
                                        <td><?php echo e($row['quantity']); ?></td>
                                        <td><?php echo e($row['minimum_stock']); ?></td>
                                        <td><?php echo e($row['expiration_date']); ?></td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center">
                                        No low stock medicines found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

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
