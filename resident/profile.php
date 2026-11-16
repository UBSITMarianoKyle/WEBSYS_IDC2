
<?php
    session_start();

    require_once "../HealthSystem.php";

    if(!isset($_SESSION['ID']) || $_SESSION['ROLE'] != 'resident'){
        header("location: ../login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);
    $user_id = $_SESSION['ID'];

    $row = $obj->show_resident_profile($user_id);

    function displayInfo($value){
        if($value === null || $value === ""){
            return "Not provided";
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | Barangay Tawang Health System</title>

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
            font-weight: bold;
            font-size: 18px;
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

        .profile-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .profile-card .card-header {
            background-color: white;
            color: #123B70;
            font-weight: bold;
            font-size: 18px;
            padding: 18px 22px;
            border-bottom: 2px solid #FFEB00;
        }

        .profile-card .card-body {
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
            white-space: pre-wrap;
        }

        .edit-btn {
            background-color: #123B70;
            color: white;
            padding: 10px 20px;
        }

        .edit-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        .logout-btn {
            border: 1px solid #123B70;
            color: #123B70;
        }

        .logout-btn:hover {
            background-color: #123B70;
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
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">

        <a href="index.php"
           class="navbar-brand d-flex align-items-center gap-2">

            <img src="../brgylogo.jpg"
                 class="barangay-logo"
                 alt="Barangay Logo">

            Barangay Tawang Health System
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="d-none d-md-inline">
                <?php echo displayInfo($_SESSION['USERNAME'] ?? 'Resident'); ?>
            </span>

            <a href="logout.php" class="btn logout-btn btn-sm">
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

            <a href="index.php">
                Dashboard
            </a>

            <a href="profile.php" class="active">
                My Profile
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

        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 main-content">

            <div class="page-header">
                <h2>My Profile</h2>
                <p class="mb-0">
                    View and manage your personal information.
                </p>
            </div>

            <?php if(isset($_GET['updated']) && $_GET['updated'] == 1): ?>

                <div class="alert alert-success">
                    Profile updated successfully!
                </div>

            <?php endif; ?>

            <?php if($row): ?>

                <!-- EDIT PROFILE -->
                <div class="d-flex justify-content-end mb-3">

                    <a href="../update.php?url_id=<?php echo (int)$row['resident_id']; ?>"
                    class="btn edit-btn">
                    Edit Profile
                    </a>

                </div>

                <!-- PERSONAL INFORMATION -->
                <div class="card profile-card shadow-sm">

                    <div class="card-header">
                        Personal Information
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="info-label">First Name</div>
                                <div class="info-value"><?php echo displayInfo($row['first_name']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Last Name</div>
                                <div class="info-value"><?php echo displayInfo($row['last_name']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Birth Date</div>
                                <div class="info-value"><?php echo displayInfo($row['birth_date']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Sex</div>
                                <div class="info-value"><?php echo displayInfo($row['sex']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Household Number</div>
                                <div class="info-value"><?php echo displayInfo($row['household_no']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value"><?php echo displayInfo($row['contact']); ?></div>
                            </div>

                            <div class="col-12">
                                <div class="info-label">Address</div>
                                <div class="info-value"><?php echo displayInfo($row['address']); ?></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- HEALTH INFORMATION -->
                <div class="card profile-card shadow-sm">

                    <div class="card-header">
                        Health Information
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="info-label">Health Condition</div>
                                <div class="info-value"><?php echo displayInfo($row['health_condition']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Allergies</div>
                                <div class="info-value"><?php echo displayInfo($row['allergies']); ?></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- EMERGENCY CONTACT -->
                <div class="card profile-card shadow-sm">

                    <div class="card-header">
                        Emergency Contact
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="info-label">Contact Person</div>
                                <div class="info-value"><?php echo displayInfo($row['emergency_contact']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value"><?php echo displayInfo($row['emergency_phone']); ?></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ACCOUNT INFORMATION -->
                <div class="card profile-card shadow-sm">

                    <div class="card-header">
                        Account Information
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="info-label">Username</div>
                                <div class="info-value"><?php echo displayInfo($_SESSION['USERNAME'] ?? ''); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">Role</div>
                                <div class="info-value">Resident</div>
                            </div>

                        </div>
                    </div>
                </div>

                <a href="index.php" class="btn btn-secondary">
                    Back to Dashboard
                </a>

            <?php else: ?>

                <div class="alert alert-warning">
                    No resident profile found.
                    Please contact the administrator.
                </div>

                <a href="index.php" class="btn btn-secondary">
                    Back to Dashboard
                </a>

            <?php endif; ?>

        </div>

    </div>
</div>

</body>
</html>
