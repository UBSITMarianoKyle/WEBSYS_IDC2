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
    <a href="index.php">Dashboard</a>
    <a href="appointments.php" class="active">Appointments</a>
    <a href="consultations.php">Consultations</a>
    <a href="vaccinations.php">Vaccinations</a>
    <a href="medicines.php">Medicine Inventory</a>
</div>

<!-- CONTENT -->
<div class="content">

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


    <div class="footer">
        Barangay Tawang Health System | Nurse Portal
    </div>

</div>

</body>
</html>
