<?php
    session_start();

    require_once __DIR__ . "/../HealthSystem.php";

    if(!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'nurse'){
        header("location: ../login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);

    date_default_timezone_set('Asia/Manila');

    $fullname = $_SESSION['USERNAME'] ?? 'Nurse';
    $error = "";

    if(!isset($_SESSION['nurse_token'])){
        $_SESSION['nurse_token'] = bin2hex(random_bytes(32));
    }

    $residents = $obj->show_residents();
    $resident_ids = array_column($residents, 'resident_id');

    if(isset($_POST['submit'])){

        $resident_id = (int)($_POST['resident_id'] ?? 0);
        $consultation_date = $_POST['consultation_date'] ?? '';
        $symptoms = trim($_POST['symptoms'] ?? '');
        $assessment = trim($_POST['assessment'] ?? '');
        $treatment = trim($_POST['treatment'] ?? '');

        if(!hash_equals($_SESSION['nurse_token'], $_POST['nurse_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if(!in_array($resident_id, $resident_ids)){
            $error = "Please select a valid resident.";
        }
        else if(!DateTime::createFromFormat('!Y-m-d\TH:i', $consultation_date)){
            $error = "Invalid consultation date.";
        }
        else if(strtotime($consultation_date) > time()){
            $error = "Consultation date cannot be in the future.";
        }
        else if($symptoms == "" || $assessment == ""){
            $error = "Symptoms and assessment are required.";
        }
        else{

            $consultation_date = str_replace("T", " ", $consultation_date) . ":00";

            $result = $obj->add_consultation(
                $resident_id,
                $_SESSION['ID'],
                $consultation_date,
                $symptoms,
                $assessment,
                $treatment
            );

            if($result){
                header("location: consultations.php?success=1");
                exit();
            }
            else{
                $error = "Failed to save consultation.";
            }
        }
    }

    $consultations = $obj->show_consultations();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Consultations | Barangay Tawang Health System</title>

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
<link rel="stylesheet" href="../mobile_additions.css"></head>

<body>

<!-- NAVBAR -->
<nav class="navbar"><button type="button" class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" onclick="document.body.classList.toggle('menu-open');this.setAttribute('aria-expanded',document.body.classList.contains('menu-open'))">☰</button>
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

            <a href="appointments.php">
                Appointments
            </a>

            <a href="consultations.php" class="active">
                Consultations
            </a>

            <a href="vaccinations.php">
                Vaccinations
            </a>
<a href="health_visits.php">Scheduled Health Visits</a>

            <a href="medicines.php">
                Medicine Inventory
            </a>

        </div>

        <!-- MAIN CONTENT -->
        <main class="col-md-9 col-lg-10 main-content">


            <h2>Consultation Management</h2>
            <p>Record resident consultations and view consultation history.</p>

            <?php if(isset($_GET['success'])): ?>
                <p class="alert alert-success">Consultation saved successfully.</p>
            <?php endif; ?>
            <?php if($error != ""): ?>
                <p class="alert alert-danger">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <!-- ADD CONSULTATION -->
            <div class="box">

                <h3>New Consultation</h3>

                <form method="post">

                    <input type="hidden" name="nurse_token" value="<?php echo $_SESSION['nurse_token']; ?>">

                    <table class="form-table">

                        <tr>
                            <th>Resident</th>
                            <td>
                                <select name="resident_id" class="form-select" required>
                                    <option value="" disabled <?php if(empty($_POST['resident_id'])) echo 'selected'; ?>>Select Resident</option>
                                    <?php foreach($residents as $r): ?>
                            <option value="<?php echo (int)$r['resident_id']; ?>"
                                <?php if(($_POST['resident_id'] ?? '') == $r['resident_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($r['first_name'] . " " . $r['last_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th>Consultation Date and Time</th>
                            <td>
                                <input type="datetime-local" name="consultation_date" class="form-control"
                                       max="<?php echo date('Y-m-d\TH:i'); ?>"
                                       value="<?php echo htmlspecialchars($_POST['consultation_date'] ?? date('Y-m-d\TH:i'), ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th>Symptoms and Complaints</th>
                            <td>
                                <textarea name="symptoms" class="form-control" rows="2" required><?php echo htmlspecialchars($_POST['symptoms'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <th>Assessment</th>
                            <td>
                                <textarea name="assessment" class="form-control" rows="2" required><?php echo htmlspecialchars($_POST['assessment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <th>Treatment</th>
                            <td>
                                <textarea name="treatment" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['treatment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2">
                                <button type="submit" name="submit" class="btn submit-btn">Save Consultation</button>
                            </td>
                        </tr>

                    </table>

                </form>

            </div>

            <!-- CONSULTATION HISTORY -->
            <div class="box">

                <h3>Consultation History</h3>

                <table class="data-table">

                    <tr>
                        <th>ID</th>
                        <th>Resident</th>
                        <th>Date and Time</th>
                        <th>Symptoms</th>
                        <th>Assessment</th>
                        <th>Treatment</th>
                    </tr>

                    <?php if(count($consultations) > 0): ?>

                        <?php foreach($consultations as $row): ?>
                            <tr>
                                <td><?php echo (int)$row['consultation_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date("M d, Y - h:i A", strtotime($row['consultation_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['symptoms'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['assessment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['treatment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="empty">No consultations found.</td>
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
