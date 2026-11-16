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
            margin: 0;
        }

        /* HEADER */
        .header {
            background-color: #FFEB00;
            border-bottom: 3px solid #123B70;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #123B70;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
        }

        .header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .header a.logout {
            color: #123B70;
            border: 1px solid #123B70;
            padding: 4px 12px;
            border-radius: 5px;
            text-decoration: none;
        }

        .header a.logout:hover {
            background-color: #123B70;
            color: white;
        }

        /* NAVIGATION */
        .nav {
            background-color: #123B70;
            padding: 10px 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            padding: 6px 4px;
        }

        .nav a:hover {
            text-decoration: underline;
        }

        .nav a.active {
            color: #FFEB00;
            font-weight: bold;
        }

        /* CONTENT */
        .content {
            max-width: 1100px;
            margin: 25px auto;
            padding: 0 15px;
        }

        h2, h3 {
            color: #123B70;
        }

        .box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
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
        }

        .data-table tr:nth-child(even) {
            background-color: #f1f4f8;
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

        .footer {
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #dee2e6;
            padding: 15px 0;
        }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">

    <a href="index.php" class="brand">
        <img src="../logo/brgylogo.jpg" alt="Barangay Logo">
        <span>Barangay Tawang Health System</span>
    </a>

    <div>
        <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>
        &nbsp;
        <a href="logout.php" class="logout">Logout</a>
    </div>

</div>

<!-- NAVIGATION -->
<div class="nav">
    <a href="index.php" class="active">Dashboard</a>
    <a href="appointments.php">Appointments</a>
    <a href="consultations.php">Consultations</a>
    <a href="vaccinations.php">Vaccinations</a>
    <a href="medicines.php">Medicine Inventory</a>
</div>

<!-- CONTENT -->
<div class="content">

    <h2>Welcome, <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>!</h2>
    <p>Manage appointments, consultations, vaccinations, and medicine stock.</p>

    <!-- SUMMARY -->
    <div class="box">

        <h3>Summary</h3>

        <table class="data-table">

            <tr>
                <th>Pending Appointments</th>
                <th>Total Consultations</th>
                <th>Vaccination Records</th>
                <th>Low-Stock Medicines</th>
            </tr>

            <tr>
                <td><?php echo (int)$stats['pending_appointments']; ?></td>
                <td><?php echo (int)$stats['total_consultations']; ?></td>
                <td><?php echo (int)$stats['total_vaccinations']; ?></td>
                <td><?php echo (int)$stats['low_stock']; ?></td>
            </tr>

        </table>

    </div>

    <!-- SERVICES -->
    <div class="box">

        <h3>Nurse Services</h3>

        <table class="data-table">

            <tr>
                <th>Service</th>
                <th>Description</th>
                <th>Open</th>
            </tr>

            <tr>
                <td>Appointments</td>
                <td>Approve or reject appointment requests from residents.</td>
                <td><a href="appointments.php">Manage</a></td>
            </tr>

            <tr>
                <td>Consultations</td>
                <td>Record symptoms, assessments, and treatments.</td>
                <td><a href="consultations.php">Manage</a></td>
            </tr>

            <tr>
                <td>Vaccinations</td>
                <td>Record vaccines, doses, next schedules, and completion status.</td>
                <td><a href="vaccinations.php">Manage</a></td>
            </tr>

            <tr>
                <td>Medicine Inventory</td>
                <td>Record stock-in, stock-out, and medicine distribution.</td>
                <td><a href="medicines.php">Manage</a></td>
            </tr>

        </table>

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


    <div class="footer">
        Barangay Tawang Health System | Nurse Portal
    </div>

</div>

</body>
</html>
