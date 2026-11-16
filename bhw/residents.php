
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Residents | BHW</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Barangay Tawang Health System | BHW
        </a>

        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link" href="consultations.php">Consultations</a>
            <a class="nav-link" href="vaccinations.php">Vaccinations</a>
            <a class="nav-link" href="maternal_child.php">Maternal & Child</a>
            <a class="btn btn-outline-light" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">

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

</div>

</body>
</html>
