
<?php
    session_start();

    require_once __DIR__ . "/../HealthSystem.php";

    if(!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'resident'){
        header("location: ../login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);

    $user_id = $_SESSION['ID'];
    $resident = $obj->show_resident_profile($user_id);

    if(!$resident){
        exit("Resident profile not found.");
    }

    $resident_id = $resident['resident_id'];
    $error = "";

    date_default_timezone_set('Asia/Manila');

    if(!isset($_SESSION['appointment_token'])){
        $_SESSION['appointment_token'] = bin2hex(random_bytes(32));
    }

    if(isset($_POST['submit'])){

        $service_type = $_POST['service_type'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');

        if(!hash_equals($_SESSION['appointment_token'], $_POST['appointment_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if($service_type == ""){
            $error = "Please select a service.";
        }
        else if(!in_array($service_type, [
            'Medical Consultation',
            'Vaccination',
            'Prenatal Checkup',
            'Child Health Checkup',
            'Other Health Service'
        ], true)){
            $error = "Invalid service type.";
        }
        else if($appointment_date == ""){
            $error = "Please select an appointment date.";
        }
        else if(!DateTime::createFromFormat('!Y-m-d\TH:i', $appointment_date)){
            $error = "Invalid appointment date.";
        }
        else if(strtotime($appointment_date) <= time()){
            $error = "Please select a future date and time.";
        }
        else{

            $appointment_date = str_replace("T", " ", $appointment_date) . ":00";

            $result = $obj->add_appointment(
                $resident_id,
                $service_type,
                $appointment_date,
                $notes
            );

            if($result){
                header("location: appointments.php?success=1");
                exit();
            }
            else{
                $error = "Failed to submit appointment.";
            }
        }
    }

    $appointments = $obj->show_resident_appointments($resident_id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Appointments | Barangay Tawang Health System</title>

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
            font-weight: bold;
            font-size: 18px;
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

        .page-header {
            background-color: #123B70;
            color: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .page-header p {
            margin-bottom: 0;
            font-size: 15px;
        }

        /* APPOINTMENT CARDS */
        .appointment-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .appointment-card .card-header {
            background-color: white;
            color: #123B70;
            font-size: 17px;
            font-weight: bold;
            padding: 14px 20px;
            border-bottom: 2px solid #FFEB00;
        }

        .form-label {
            color: #123B70;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #123B70;
            box-shadow: 0 0 0 0.15rem rgba(18, 59, 112, 0.15);
        }

        /* BUTTONS */
        .submit-btn {
            background-color: #123B70;
            color: white;
            padding: 9px 20px;
        }

        .submit-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        /* TABLE */
        .table thead th {
            background-color: #f8f9fa;
            color: #123B70;
            font-weight: bold;
        }

        .table td,
        .table th {
            padding: 12px;
            vertical-align: middle;
        }

        .table {
            margin-bottom: 0;
        }

        /* MOBILE */
        @media(max-width: 767px){

            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 20px 15px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h2 {
                font-size: 23px;
            }

            .navbar-brand {
                font-size: 14px;
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

            <img src="../brgylogo.jpg"
                 class="barangay-logo"
                 alt="Barangay Logo">

            <span>Barangay Tawang Health System</span>
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="d-none d-md-inline">
                <?php echo htmlspecialchars($_SESSION['USERNAME'] ?? 'Resident', ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <a href="logout.php" class="btn logout-btn btn-sm">
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

            <h5>Resident Portal</h5>

            <a href="index.php">
                Dashboard
            </a>

            <a href="profile.php">
                My Profile
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

        </div>

        <!-- MAIN CONTENT -->
        <main class="col-md-9 col-lg-10 main-content">

            <!-- PAGE HEADER -->
            <div class="page-header">

                <h2>My Appointments</h2>

                <p>
                    Request an appointment and view your appointment status.
                </p>

            </div>

            <!-- SUCCESS MESSAGE -->
            <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>

                <div class="alert alert-success">
                    Appointment request submitted successfully!
                </div>

            <?php endif; ?>

            <!-- ERROR MESSAGE -->
            <?php if($error != ""): ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>

            <?php endif; ?>

            <!-- APPOINTMENT FORM -->
            <div class="card appointment-card shadow-sm">

                <div class="card-header">
                    Request an Appointment
                </div>

                <div class="card-body p-3">

                    <form method="post">

                        <input type="hidden"
                               name="appointment_token"
                               value="<?php echo htmlspecialchars($_SESSION['appointment_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="row g-3">

                            <!-- SERVICE TYPE -->
                            <div class="col-md-6">

                                <label class="form-label">
                                    Service Type
                                </label>

                                <select name="service_type"
                                        class="form-select"
                                        required>

                                    <option value="" disabled
                                        <?php if(empty($_POST['service_type'])) echo 'selected'; ?>>
                                        Select Service
                                    </option>

                                    <option value="Medical Consultation"
                                        <?php if(($_POST['service_type'] ?? '') == 'Medical Consultation') echo 'selected'; ?>>
                                        Medical Consultation
                                    </option>

                                    <option value="Vaccination"
                                        <?php if(($_POST['service_type'] ?? '') == 'Vaccination') echo 'selected'; ?>>
                                        Vaccination
                                    </option>

                                    <option value="Prenatal Checkup"
                                        <?php if(($_POST['service_type'] ?? '') == 'Prenatal Checkup') echo 'selected'; ?>>
                                        Prenatal Checkup
                                    </option>

                                    <option value="Child Health Checkup"
                                        <?php if(($_POST['service_type'] ?? '') == 'Child Health Checkup') echo 'selected'; ?>>
                                        Child Health Checkup
                                    </option>

                                    <option value="Other Health Service"
                                        <?php if(($_POST['service_type'] ?? '') == 'Other Health Service') echo 'selected'; ?>>
                                        Other Health Service
                                    </option>

                                </select>

                            </div>

                            <!-- APPOINTMENT DATE -->
                            <div class="col-md-6">

                                <label class="form-label">
                                    Appointment Date and Time
                                </label>

                                <input type="datetime-local"
                                       name="appointment_date"
                                       class="form-control"
                                       min="<?php echo date('Y-m-d\TH:i'); ?>"
                                       value="<?php echo htmlspecialchars($_POST['appointment_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>

                            </div>

                            <!-- NOTES -->
                            <div class="col-12">

                                <label class="form-label">
                                    Additional Notes
                                </label>

                                <textarea name="notes"
                                          class="form-control"
                                          rows="2"
                                          placeholder="Enter additional information (optional)"><?php echo htmlspecialchars($_POST['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                            </div>

                            <!-- SUBMIT BUTTON -->
                            <div class="col-12 text-end">

                                <button type="submit"
                                        name="submit"
                                        class="btn submit-btn">

                                    Submit Appointment

                                </button>

                            </div>

                        </div>

                    </form>

                </div>
            </div>

            <!-- APPOINTMENT HISTORY -->
            <div class="card appointment-card shadow-sm">

                <div class="card-header">
                    Appointment History
                </div>

                <div class="card-body p-3">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>
                                <tr>
                                    <th>Appointment ID</th>
                                    <th>Service Type</th>
                                    <th>Date and Time</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if(count($appointments) > 0): ?>

                                    <?php foreach($appointments as $row): ?>

                                        <tr>

                                            <td>
                                                <?php echo (int)$row['appointment_id']; ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($row['service_type'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?php
                                                    echo date(
                                                        "M d, Y - h:i A",
                                                        strtotime($row['appointment_date'])
                                                    );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                    $status = $row['status'];

                                                    if($status == 'pending'){
                                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                    }
                                                    else if($status == 'approved'){
                                                        echo '<span class="badge bg-success">Approved</span>';
                                                    }
                                                    else if($status == 'rejected'){
                                                        echo '<span class="badge bg-danger">Rejected</span>';
                                                    }
                                                    else if($status == 'completed'){
                                                        echo '<span class="badge bg-primary">Completed</span>';
                                                    }
                                                    else{
                                                        echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
                                                    }
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                    echo htmlspecialchars($row['notes'] ?? '', ENT_QUOTES, 'UTF-8');
                                                ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="5"
                                            class="text-center text-muted py-4">

                                            No appointments found.

                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        </main>

    </div>
</div>

</body>
</html>
