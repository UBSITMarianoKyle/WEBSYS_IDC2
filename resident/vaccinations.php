
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

    $vaccinations = $obj->show_resident_vaccinations($resident_id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Vaccinations | Barangay Tawang Health System</title>

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
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            text-decoration: none;
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
            padding: 30px;
        }

        .page-header {
            background-color: #123B70;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-weight: bold;
        }

        .vaccination-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .vaccination-card .card-header {
            background-color: white;
            color: #123B70;
            font-weight: bold;
            padding: 18px 22px;
            border-bottom: 2px solid #FFEB00;
        }

        .vaccination-card .card-body {
            padding: 22px;
        }

        .info-label {
            color: #123B70;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .info-value {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            min-height: 43px;
            overflow-wrap: anywhere;
        }

        .back-btn {
            background-color: #123B70;
            color: white;
        }

        .back-btn:hover {
            background-color: #0B284D;
            color: white;
        }

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
        }
    </style>
<link rel="stylesheet" href="../mobile_additions.css"></head>

<body>

<!-- NAVBAR -->
<nav class="navbar"><button type="button" class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" onclick="document.body.classList.toggle('menu-open');this.setAttribute('aria-expanded',document.body.classList.contains('menu-open'))">☰</button>
    <div class="container-fluid d-flex justify-content-between align-items-center">

        <a href="index.php"
           class="navbar-brand d-flex align-items-center gap-2">

            <img src="../brgylogo.png"
                 class="barangay-logo"
                 alt="Barangay Logo">

            Barangay Tawang Health System
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="d-none d-md-inline">
                <?php echo htmlspecialchars($_SESSION['USERNAME'] ?? 'Resident', ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <a href="logout.php" class="btn btn-outline-dark btn-sm">
                Logout
            </a>

        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row g-0">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar">

            <h5>Resident Portal</h5>

            <a href="index.php">Dashboard</a>

            <a href="profile.php">My Profile</a>

            <a href="appointments.php">Appointments</a>

            <a href="consultations.php">Consultations</a>

            <a href="vaccinations.php" class="active">
                Vaccinations
            </a>

        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 main-content">

            <div class="page-header">
                <h2>My Vaccinations</h2>
                <p class="mb-0">
                    View your vaccination records and upcoming schedules.
                </p>
            </div>

            <?php if(count($vaccinations) > 0): ?>

                <?php foreach($vaccinations as $row): ?>

                    <div class="card vaccination-card shadow-sm">

                        <div class="card-header">
                            <?php echo htmlspecialchars($row['vaccine_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="info-label">
                                        Vaccination ID
                                    </div>

                                    <div class="info-value">
                                        <?php echo (int)$row['vaccination_id']; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">
                                        Vaccine Name
                                    </div>

                                    <div class="info-value">
                                        <?php echo htmlspecialchars($row['vaccine_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">
                                        Dose Number
                                    </div>

                                    <div class="info-value">
                                        <?php echo htmlspecialchars($row['dose_number'] ?? 'Not provided', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">
                                        Vaccination Date
                                    </div>

                                    <div class="info-value">
                                        <?php
                                            if(!empty($row['vaccination_date'])){
                                                echo date("F d, Y", strtotime($row['vaccination_date']));
                                            }
                                            else{
                                                echo "Not provided";
                                            }
                                        ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">
                                        Next Schedule
                                    </div>

                                    <div class="info-value">
                                        <?php
                                            if(!empty($row['next_schedule'])){
                                                echo date("F d, Y", strtotime($row['next_schedule']));
                                            }
                                            else{
                                                echo "No schedule";
                                            }
                                        ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-label">
                                        Status
                                    </div>

                                    <div class="info-value">
                                        <?php echo htmlspecialchars($row['status'] ?? 'Not provided', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="card vaccination-card shadow-sm">
                    <div class="card-body text-center py-5">

                        <h5 class="text-secondary">
                            No Vaccination Records Found
                        </h5>

                        <p class="text-muted">
                            You do not have any vaccination records yet.
                        </p>

                        <a href="appointments.php" class="btn back-btn">
                            Request an Appointment
                        </a>

                    </div>
                </div>

            <?php endif; ?>

            <a href="index.php" class="btn btn-secondary mt-2">
                Back to Dashboard
            </a>

        </div>
    </div>
</div>

</body>
</html>
