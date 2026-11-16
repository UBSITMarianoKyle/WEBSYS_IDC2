
<?php
session_start();
require_once "../HealthSystem.php";

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "bhw") {
    header("Location: ../login.php");
    exit();
}

$healthSystem = new HealthSystem($pdo);

$message = "";

if (isset($_POST["save_consultation"])) {

    $resident_id = $_POST["resident_id"];
    $date = $_POST["consultation_date"];
    $symptoms = trim($_POST["symptoms"]);
    $assessment = trim($_POST["assessment"]);
    $treatment = trim($_POST["treatment"]);

    if ($resident_id && $date && $symptoms != "") {

        $result = $healthSystem->add_consultation(
            $resident_id,
            $_SESSION["ID"],
            $date,
            $symptoms,
            $assessment,
            $treatment
        );

        $message = $result
            ? "Consultation saved successfully!"
            : "Failed to save consultation.";

    } else {
        $message = "Please complete the required fields.";
    }
}

$residents = $healthSystem->show_residents();
$consultations = $healthSystem->show_consultations();

function h($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Consultations | BHW</title>

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
            <a class="nav-link" href="residents.php">Residents</a>
            <a class="nav-link" href="vaccinations.php">Vaccinations</a>
            <a class="nav-link" href="maternal_child.php">Maternal & Child</a>
            <a class="btn btn-outline-light" href="logout.php">Logout</a>
        </div>

    </div>
</nav>

<div class="container py-4">

    <h2 class="section-title">Consultations</h2>

    <p class="text-muted">
        Record and manage resident consultations.
    </p>

    <?php if ($message): ?>
        <div class="alert alert-info">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">

        <div class="card-header fw-bold">
            Add Consultation
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Resident</label>

                        <select name="resident_id" class="form-select" required>
                            <option value="">Select Resident</option>

                            <?php foreach ($residents as $resident): ?>

                                <option value="<?= h($resident["resident_id"]) ?>">
                                    <?= h($resident["first_name"] . " " . $resident["last_name"]) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Consultation Date</label>

                        <input type="date"
                               name="consultation_date"
                               class="form-control"
                               value="<?= date("Y-m-d") ?>"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Symptoms</label>

                        <textarea name="symptoms"
                                  class="form-control"
                                  rows="2"
                                  required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assessment</label>

                        <textarea name="assessment"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Treatment</label>

                        <textarea name="treatment"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit"
                                name="save_consultation"
                                class="btn btn-primary">

                            Save Consultation

                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-header fw-bold">
            Consultation History
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Resident</th>
                            <th>Symptoms</th>
                            <th>Assessment</th>
                            <th>Treatment</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($consultations)): ?>

                        <tr>
                            <td colspan="5" class="text-center">
                                No consultation records found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($consultations as $consultation): ?>

                        <tr>
                            <td><?= h($consultation["consultation_date"]) ?></td>

                            <td>
                                <?= h($consultation["first_name"] . " " . $consultation["last_name"]) ?>
                            </td>

                            <td><?= h($consultation["symptoms"]) ?></td>
                            <td><?= h($consultation["assessment"]) ?></td>
                            <td><?= h($consultation["treatment"]) ?></td>
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
