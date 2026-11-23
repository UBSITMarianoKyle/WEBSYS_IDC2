
<?php
session_start();
require_once "../HealthSystem.php";

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "bhw") {
    header("Location: ../login.php");
    exit();
}

$healthSystem = new HealthSystem($pdo);
$residents = $healthSystem->show_residents();

function h($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Residents | BHW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{background:#f8f9fa;font-family:Arial,sans-serif}
        .navbar{background:#ffeb00;border-bottom:3px solid #123b70;padding:10px 20px}
        .navbar-brand{color:#123b70;font-size:18px;font-weight:bold}
        .barangay-logo{width:45px;height:45px;border-radius:50%;object-fit:cover}
        .logout-btn{border:1px solid #123b70;color:#123b70}
        .logout-btn:hover{background:#123b70;color:white}
        .sidebar{background:#123b70;min-height:calc(100vh - 73px);padding:20px 15px}
        .sidebar h5{color:white;font-size:18px;font-weight:bold;margin-bottom:25px}
        .sidebar a{display:block;color:white;text-decoration:none;padding:12px 15px;margin-bottom:8px;border-radius:6px;font-size:15px}
        .sidebar a:hover{background:#244f83;color:white}
        .sidebar a.active{background:#ffeb00;color:#123b70;font-weight:bold}
        .main-content{padding:25px}
        .welcome-header{background:#123b70;color:white;padding:25px;border-radius:10px;margin-bottom:25px}
        .section-title{color:#123b70;font-size:23px;font-weight:bold}
        .card{border:1px solid #e9ecef;border-radius:10px}
        .card-header{background:#f1f5fa;color:#123b70}
        .btn-primary{background:#123b70;border-color:#123b70}
        .btn-primary:hover{background:#244f83}
        .table thead th{background:#123b70;color:white}
    </style>
</head>

<body>

    <nav class="navbar d-flex align-items-center">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img class="barangay-logo" src="../logo/barangay ambiong health center logo.jpg" alt="Barangay logo">
            Barangay Tawang Health System | BHW
        </a>

        <a href="../logout.php" class="btn logout-btn ms-auto">Logout</a>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <aside class="col-md-3 col-lg-2 sidebar">
                <h5>BHW Menu</h5>

                <a class="" href="index.php">Dashboard</a>
                <a class="active" href="residents.php">Residents</a>
                <a class="" href="consultations.php">Consultations</a>
                <a class="" href="vaccinations.php">Vaccinations</a>
                <a class="" href="maternal_child.php">Maternal & Child</a>
            </aside>

            <main class="col-md-9 col-lg-10 main-content">

                <h2 class="section-title">Resident Records</h2>

                <p class="text-muted">
                    List of residents registered in the health system.
                </p>

                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-striped table-hover">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Birth Date</th>
                                        <th>Sex</th>
                                        <th>Address</th>
                                        <th>Household No.</th>
                                        <th>Contact</th>
                                        <th>Health Condition</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if (empty($residents)): ?>

                                        <tr>
                                            <td colspan="8" class="text-center">
                                                No residents found.
                                            </td>
                                        </tr>

                                    <?php else: ?>

                                        <?php foreach ($residents as $index => $resident): ?>

                                            <tr>
                                                <td><?= $index + 1 ?></td>

                                                <td>
                                                    <?= h($resident["first_name"] . " " . $resident["last_name"]) ?>
                                                </td>

                                                <td><?= h($resident["birth_date"]) ?></td>
                                                <td><?= h($resident["sex"]) ?></td>
                                                <td><?= h($resident["address"]) ?></td>
                                                <td><?= h($resident["household_no"]) ?></td>
                                                <td><?= h($resident["contact"]) ?></td>
                                                <td><?= h($resident["health_condition"]) ?></td>
                                            </tr>

                                        <?php endforeach; ?>

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