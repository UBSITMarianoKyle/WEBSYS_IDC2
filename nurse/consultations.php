<?php
    session_start();

    require_once __DIR__ . "/../HealthSystem.php";

    if(!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'nurse'){
        header("location: ../login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);

    $fullname = $_SESSION['USERNAME'] ?? 'Nurse';
    $error = "";

    if(!isset($_SESSION['nurse_token'])){
        $_SESSION['nurse_token'] = bin2hex(random_bytes(32));
    }

    if(isset($_POST['update_status'])){

        $appointment_id = (int)($_POST['appointment_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';

        $found = $obj->show_updated_appointment($appointment_id);
        $current = $found[0]['status'] ?? '';

        // pending -> approved / rejected, approved -> completed
        $allowed = [
            'pending'  => ['approved', 'rejected'],
            'approved' => ['completed']
        ];

        if(!hash_equals($_SESSION['nurse_token'], $_POST['nurse_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if(!$found){
            $error = "Appointment not found.";
        }
        else if(!in_array($new_status, $allowed[$current] ?? [], true)){
            $error = "That status change is not allowed.";
        }
        else{
            $obj->update_appointment($appointment_id, $new_status, $_SESSION['ID']);

            header("location: appointments.php?updated=1");
            exit();
        }
    }

    $appointments = $obj->show_appointments();


    $filter = isset($_POST['filter']) ? ($_POST['status'] ?? 'all') : 'all';

    if(!in_array($filter, ['all', 'pending', 'approved', 'rejected', 'completed'], true)){
        $filter = 'all';
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Appointments | Barangay Tawang Health System</title>

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

            <a href="index.php">
                Dashboard
            </a>

            <a href="appointments.php" class="active">
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


            <h2>Appointment Management</h2>
            <p>Approve or reject appointment requests and view appointment schedules.</p>

            <?php if(isset($_GET['updated'])): ?>
                <p class="alert alert-success">Appointment updated successfully.</p>
            <?php endif; ?>
            <?php if($error != ""): ?>
                <p class="alert alert-danger">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <div class="box">

                <h3>Appointment Requests</h3>

                <!-- FILTER -->
                <form method="post">

                    <table class="form-table">
                        <tr>
                            <th>Show Status</th>
                            <td>
                                <select name="status" class="form-select">
                                    <?php foreach(['all', 'pending', 'approved', 'rejected', 'completed'] as $s): ?>
                                        <option value="<?php echo $s; ?>"
                                            <?php if($filter == $s) echo 'selected'; ?>>
                                            <?php echo ucfirst($s); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <button type="submit" name="filter" class="btn submit-btn">Filter</button>
                            </td>
                        </tr>
                    </table>

                </form>

                <!-- APPOINTMENT TABLE -->
                <table class="data-table">

                    <tr>
                        <th>ID</th>
                        <th>Resident</th>
                        <th>Service Type</th>
                        <th>Date and Time</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                    <?php $shown = 0; ?>

                    <?php foreach($appointments as $row): ?>

                        <?php
                            if($filter != 'all' && $row['status'] != $filter){
                                continue;
                            }
                            $shown++;
                        ?>

                        <tr>
                            <td><?php echo (int)$row['appointment_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo date("M d, Y - h:i A", strtotime($row['appointment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['status']), ENT_QUOTES, 'UTF-8'); ?></td>

                            <td>
                                <?php if($row['status'] == 'pending'): ?>

                                    <form method="post">
                                        <input type="hidden" name="nurse_token" value="<?php echo $_SESSION['nurse_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                        <input type="hidden" name="new_status" value="approved">
                                        <button type="submit" name="update_status" class="btn btn-success btn-sm">Approve</button>
                                    </form>

                                    <form method="post">
                                        <input type="hidden" name="nurse_token" value="<?php echo $_SESSION['nurse_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                        <input type="hidden" name="new_status" value="rejected">
                                        <button type="submit" name="update_status" class="btn btn-danger btn-sm">Reject</button>
                                    </form>

                                <?php elseif($row['status'] == 'approved'): ?>

                                    <form method="post">
                                        <input type="hidden" name="nurse_token" value="<?php echo $_SESSION['nurse_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                        <input type="hidden" name="new_status" value="completed">
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Mark Completed</button>
                                    </form>

                                <?php else: ?>

                                    -

                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <?php if($shown == 0): ?>
                        <tr>
                            <td colspan="7" class="empty">No appointments found.</td>
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
