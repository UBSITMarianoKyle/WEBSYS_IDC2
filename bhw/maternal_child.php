
<?php
session_start();
require_once "../HealthSystem.php";

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "bhw") {
    header("Location: ../login.php");
    exit();
}

$healthSystem = new HealthSystem($pdo);
$message = "";

if (isset($_POST["save_pregnancy"])) {
    $resident_id = $_POST["resident_id"];
    $registration_date = $_POST["registration_date"];
    $expected_delivery = $_POST["expected_delivery"];
    $status = $_POST["pregnancy_status"];

    $result = $healthSystem->add_pregnancy(
        $resident_id,
        $registration_date,
        $expected_delivery,
        $status
    );

    $message = $result
        ? "Pregnancy record saved successfully!"
        : "Failed to save pregnancy record.";
}

if (isset($_POST["save_prenatal"])) {
    $result = $healthSystem->add_prenatal_visit(
        $_POST["pregnancy_id"],
        $_POST["visit_date"],
        trim($_POST["blood_pressure"]),
        $_POST["weight"] ?: null,
        trim($_POST["prenatal_notes"]),
        $_POST["next_visit"] ?: null,
        $_SESSION["ID"]
    );

    $message = $result
        ? "Prenatal visit saved successfully!"
        : "Failed to save prenatal visit.";
}

if (isset($_POST["save_child"])) {
    $result = $healthSystem->add_child_health(
        $_POST["child_resident_id"],
        $_POST["child_visit_date"],
        $_POST["height"] ?: null,
        $_POST["child_weight"] ?: null,
        trim($_POST["child_notes"]),
        $_POST["child_next_visit"] ?: null
    );

    $message = $result
        ? "Child health record saved successfully!"
        : "Failed to save child health record.";
}

$residents = $healthSystem->show_residents();
$pregnancies = $healthSystem->show_pregnancies();
$prenatalVisits = $healthSystem->show_prenatal_visits();
$childRecords = $healthSystem->show_child_health();

function h($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maternal and Child Health | BHW</title>
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
                <a class="" href="residents.php">Residents</a>
                <a class="" href="consultations.php">Consultations</a>
                <a class="" href="vaccinations.php">Vaccinations</a>
                <a class="active" href="maternal_child.php">Maternal & Child</a>
            </aside>

            <main class="col-md-9 col-lg-10 main-content">

                <h2 class="section-title mb-4">Maternal and Child Health</h2>

                <p class="text-muted">
                    Manage pregnancy, prenatal, and child health records.
                </p>

                <?php if ($message): ?>
                    <div class="alert alert-info">
                        <?= h($message) ?>
                    </div>
                <?php endif; ?>

                <!-- PREGNANCY REGISTRATION -->

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">
                        Register Pregnancy
                    </div>

                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Mother / Resident</label>

                                    <select name="resident_id" class="form-select" required>
                                        <option value="">Select Resident</option>

                                        <?php foreach ($residents as $resident): ?>
                                            <option value="<?= h($resident["resident_id"]) ?>">
                                                <?= h($resident["first_name"] . " " . $resident["last_name"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Registration Date</label>

                                    <input type="date"
                                           name="registration_date"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Expected Delivery</label>

                                    <input type="date"
                                           name="expected_delivery"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Status</label>

                                    <select name="pregnancy_status" class="form-select">
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <button type="submit"
                                            name="save_pregnancy"
                                            class="btn btn-primary">
                                        Save Pregnancy
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- PRENATAL VISIT -->

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">
                        Record Prenatal Visit
                    </div>

                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Mother</label>

                                    <select name="pregnancy_id" class="form-select" required>
                                        <option value="">Select Mother</option>

                                        <?php foreach ($pregnancies as $pregnancy): ?>
                                            <option value="<?= h($pregnancy["pregnancy_id"]) ?>">
                                                <?= h($pregnancy["first_name"] . " " . $pregnancy["last_name"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Visit Date</label>

                                    <input type="date"
                                           name="visit_date"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Blood Pressure</label>

                                    <input type="text"
                                           name="blood_pressure"
                                           class="form-control"
                                           placeholder="120/80">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Weight (kg)</label>

                                    <input type="number"
                                           step="0.01"
                                           name="weight"
                                           class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Next Visit</label>

                                    <input type="date"
                                           name="next_visit"
                                           class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>

                                    <textarea name="prenatal_notes"
                                              class="form-control"
                                              rows="2"></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit"
                                            name="save_prenatal"
                                            class="btn btn-primary">
                                        Save Prenatal Visit
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- CHILD HEALTH -->

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">
                        Record Child Health Visit
                    </div>

                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Child / Resident</label>

                                    <select name="child_resident_id" class="form-select" required>
                                        <option value="">Select Resident</option>

                                        <?php foreach ($residents as $resident): ?>
                                            <option value="<?= h($resident["resident_id"]) ?>">
                                                <?= h($resident["first_name"] . " " . $resident["last_name"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Visit Date</label>

                                    <input type="date"
                                           name="child_visit_date"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Height (cm)</label>

                                    <input type="number"
                                           step="0.01"
                                           name="height"
                                           class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Weight (kg)</label>

                                    <input type="number"
                                           step="0.01"
                                           name="child_weight"
                                           class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Next Visit</label>

                                    <input type="date"
                                           name="child_next_visit"
                                           class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>

                                    <textarea name="child_notes"
                                              class="form-control"
                                              rows="2"></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit"
                                            name="save_child"
                                            class="btn btn-primary">
                                        Save Child Health Record
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- PREGNANCY HISTORY -->

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">
                        Pregnancy Records
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">

                                <thead>
                                    <tr>
                                        <th>Mother</th>
                                        <th>Registration Date</th>
                                        <th>Expected Delivery</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (empty($pregnancies)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                No pregnancy records found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pregnancies as $p): ?>
                                            <tr>
                                                <td><?= h($p["first_name"] . " " . $p["last_name"]) ?></td>
                                                <td><?= h($p["registration_date"]) ?></td>
                                                <td><?= h($p["expected_delivery"]) ?></td>
                                                <td><?= h($p["status"]) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

                <!-- PRENATAL HISTORY -->

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">
                        Prenatal Visit History
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">

                                <thead>
                                    <tr>
                                        <th>Mother</th>
                                        <th>Visit Date</th>
                                        <th>Blood Pressure</th>
                                        <th>Weight</th>
                                        <th>Next Visit</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (empty($prenatalVisits)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No prenatal visits found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($prenatalVisits as $visit): ?>
                                            <tr>
                                                <td><?= h($visit["first_name"] . " " . $visit["last_name"]) ?></td>
                                                <td><?= h($visit["visit_date"]) ?></td>
                                                <td><?= h($visit["blood_pressure"]) ?></td>
                                                <td><?= h($visit["weight"]) ?></td>
                                                <td><?= h($visit["next_visit"]) ?></td>
                                                <td><?= h($visit["notes"]) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

                <!-- CHILD HEALTH HISTORY -->

                <div class="card shadow-sm">
                    <div class="card-header fw-bold">
                        Child Health Records
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">

                                <thead>
                                    <tr>
                                        <th>Child</th>
                                        <th>Visit Date</th>
                                        <th>Height</th>
                                        <th>Weight</th>
                                        <th>Next Visit</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (empty($childRecords)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No child health records found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($childRecords as $child): ?>
                                            <tr>
                                                <td><?= h($child["first_name"] . " " . $child["last_name"]) ?></td>
                                                <td><?= h($child["visit_date"]) ?></td>
                                                <td><?= h($child["height"]) ?></td>
                                                <td><?= h($child["weight"]) ?></td>
                                                <td><?= h($child["next_visit"]) ?></td>
                                                <td><?= h($child["notes"]) ?></td>
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