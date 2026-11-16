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
    <a href="appointments.php">Appointments</a>
    <a href="consultations.php" class="active">Consultations</a>
    <a href="vaccinations.php">Vaccinations</a>
    <a href="medicines.php">Medicine Inventory</a>
</div>

<!-- CONTENT -->
<div class="content">

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


    <div class="footer">
        Barangay Tawang Health System | Nurse Portal
    </div>

</div>

</body>
</html>
