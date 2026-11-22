
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

<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Consultations | BHW</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f8f9fa;font-family:Arial,sans-serif}.navbar{background:#ffeb00;border-bottom:3px solid #123b70;padding:10px 20px}.navbar-brand{color:#123b70;font-size:18px;font-weight:bold}.barangay-logo{width:45px;height:45px;border-radius:50%;object-fit:cover}.logout-btn{border:1px solid #123b70;color:#123b70}.logout-btn:hover{background:#123b70;color:white}.sidebar{background:#123b70;min-height:calc(100vh - 73px);padding:20px 15px}.sidebar h5{color:white;font-size:18px;font-weight:bold;margin-bottom:25px}.sidebar a{display:block;color:white;text-decoration:none;padding:12px 15px;margin-bottom:8px;border-radius:6px;font-size:15px}.sidebar a:hover{background:#244f83;color:white}.sidebar a.active{background:#ffeb00;color:#123b70;font-weight:bold}.main-content{padding:25px}.welcome-header{background:#123b70;color:white;padding:25px;border-radius:10px;margin-bottom:25px}.section-title{color:#123b70;font-size:23px;font-weight:bold}.card{border:1px solid #e9ecef;border-radius:10px}.card-header{background:#f1f5fa;color:#123b70}.btn-primary{background:#123b70;border-color:#123b70}.btn-primary:hover{background:#244f83}.table thead th{background:#123b70;color:white}</style><link rel="stylesheet" href="../mobile_additions.css"></head><body>
<nav class="navbar d-flex align-items-center"><button type="button" class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" onclick="document.body.classList.toggle('menu-open');this.setAttribute('aria-expanded',document.body.classList.contains('menu-open'))">☰</button><a class="navbar-brand d-flex align-items-center gap-2" href="index.php"><img class="barangay-logo" src="../brgylogo.png" alt="Barangay logo">Barangay Tawang Health System | BHW</a><a href="../logout.php" class="btn logout-btn ms-auto">Logout</a></nav>
<div class="container-fluid"><div class="row"><aside class="col-md-3 col-lg-2 sidebar"><h5>BHW Menu</h5><a class="" href="index.php">Dashboard</a>
<a class="" href="residents.php">Residents</a>
<a href="households.php">Households</a>
<a href="health_visits.php">Scheduled Health Visits</a>
<a class="active" href="consultations.php">Consultations</a>
<a class="" href="vaccinations.php">Vaccinations</a>
<a class="" href="maternal_child.php">Maternal & Child</a><a href="medical_supplies.php">Medical Supplies</a>
<a href="patient_records.php">Patient Records</a>
<a href="appointments.php">Appointments</a>
</aside><main class="col-md-9 col-lg-10 main-content"><h2 class="section-title mb-4">Consultations</h2>


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

</main></div></div></body></html>