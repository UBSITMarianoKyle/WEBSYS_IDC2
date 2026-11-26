
<?php
    session_start();

    require_once "HealthSystem.php";

    if(!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'resident'){
        header("location: login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);
    $url_id = $_GET['url_id'] ?? 0;

    $row = $obj->show_updated_resident($url_id);

    if(!$row || $row['user_id'] != $_SESSION['ID']){
        exit("Access denied.");
    }

    if(!isset($_SESSION['update_token'])){
        $_SESSION['update_token'] = bin2hex(random_bytes(32));
    }

    $error = "";

    if(isset($_POST['edit'])){

        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $birth_date = $_POST['birth_date'];
        $sex = $_POST['sex'];
        $address = trim($_POST['address']);
        $household_no = trim($_POST['household_no']);
        $contact = trim($_POST['contact']);
        $health_condition = trim($_POST['health_condition']);
        $allergies = trim($_POST['allergies']);
        $emergency_contact = trim($_POST['emergency_contact']);
        $emergency_phone = trim($_POST['emergency_phone']);

        if(!hash_equals($_SESSION['update_token'], $_POST['update_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if($fname == "" || $lname == ""){
            $error = "First name and last name are required.";
        }
        else if($birth_date != "" &&
            (!DateTime::createFromFormat('!Y-m-d', $birth_date) ||
             $birth_date > date('Y-m-d'))){
            $error = "Invalid birth date.";
        }
        else if(!in_array($sex, ['', 'Male', 'Female', 'Other'], true)){
            $error = "Invalid sex.";
        }
        else if(strlen($fname) > 100 || strlen($lname) > 100 ||
                strlen($household_no) > 30 || strlen($contact) > 20 ||
                strlen($emergency_contact) > 100 || strlen($emergency_phone) > 20){
            $error = "One or more fields are too long.";
        }
        else{

            $obj->update_resident(
                $url_id,
                $fname,
                $lname,
                $birth_date,
                $sex,
                $address,
                $household_no,
                $contact,
                $health_condition,
                $allergies,
                $emergency_contact,
                $emergency_phone
            );

            header("location: resident/profile.php?updated=1");
            exit();
        }

        // Keep entered values when validation fails
        $row['first_name'] = $fname;
        $row['last_name'] = $lname;
        $row['birth_date'] = $birth_date;
        $row['sex'] = $sex;
        $row['address'] = $address;
        $row['household_no'] = $household_no;
        $row['contact'] = $contact;
        $row['health_condition'] = $health_condition;
        $row['allergies'] = $allergies;
        $row['emergency_contact'] = $emergency_contact;
        $row['emergency_phone'] = $emergency_phone;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Update Resident Profile</title>

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
            padding: 12px 20px;
        }

        .navbar-brand {
            color: #123B70;
            font-weight: bold;
        }

        .container-box {
            max-width: 850px;
            margin: 30px auto;
            padding: 25px;
        }

        .page-header {
            background-color: #123B70;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .profile-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .profile-card .card-header {
            background-color: white;
            color: #123B70;
            font-weight: bold;
            border-bottom: 2px solid #FFEB00;
            padding: 15px 20px;
        }

        .profile-card .card-body {
            padding: 20px;
        }

        .form-label {
            color: #123B70;
            font-weight: bold;
        }

        .save-btn {
            background-color: #123B70;
            color: white;
            padding: 10px 20px;
        }

        .save-btn:hover {
            background-color: #0B284D;
            color: white;
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="container-fluid">

        <a href="resident/profile.php" class="navbar-brand">
            Barangay Tawang Health System
        </a>

    </div>
</nav>

<div class="container-box">

    <div class="page-header">
        <h3>Update Resident Profile</h3>
        <p class="mb-0">Edit your personal information.</p>
    </div>

    <?php if($error != ""): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <input type="hidden" name="ID"
               value="<?php echo (int)$row['resident_id']; ?>">

        <input type="hidden" name="update_token"
               value="<?php echo htmlspecialchars($_SESSION['update_token'], ENT_QUOTES, 'UTF-8'); ?>">

        <!-- PERSONAL INFORMATION -->
        <div class="card profile-card">

            <div class="card-header">
                Personal Information
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">First Name</label>

                        <input type="text" name="fname"
                               class="form-control" maxlength="100" required
                               value="<?php echo htmlspecialchars($row['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>

                        <input type="text" name="lname"
                               class="form-control" maxlength="100" required
                               value="<?php echo htmlspecialchars($row['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Birth Date</label>

                        <input type="date" name="birth_date"
                               class="form-control"
                               max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($row['birth_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sex</label>

                        <select name="sex" class="form-select">

                            <option value="">Select Sex</option>

                            <option value="Male"
                                <?php if($row['sex'] == 'Male') echo 'selected'; ?>>
                                Male
                            </option>

                            <option value="Female"
                                <?php if($row['sex'] == 'Female') echo 'selected'; ?>>
                                Female
                            </option>

                            <option value="Other"
                                <?php if($row['sex'] == 'Other') echo 'selected'; ?>>
                                Other
                            </option>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Household Number</label>

                        <input type="text" name="household_no"
                               class="form-control" maxlength="30"
                               value="<?php echo htmlspecialchars($row['household_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>

                        <input type="text" name="contact"
                               class="form-control" maxlength="20"
                               value="<?php echo htmlspecialchars($row['contact'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>

                        <textarea name="address"
                                  class="form-control"
                                  rows="2"><?php echo htmlspecialchars($row['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- HEALTH INFORMATION -->
        <div class="card profile-card">

            <div class="card-header">
                Health Information
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Health Condition</label>

                        <textarea name="health_condition"
                                  class="form-control"
                                  rows="3"><?php echo htmlspecialchars($row['health_condition'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Allergies</label>

                        <textarea name="allergies"
                                  class="form-control"
                                  rows="3"><?php echo htmlspecialchars($row['allergies'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- EMERGENCY CONTACT -->
        <div class="card profile-card">

            <div class="card-header">
                Emergency Contact
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>

                        <input type="text" name="emergency_contact"
                               class="form-control" maxlength="100"
                               value="<?php echo htmlspecialchars($row['emergency_contact'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>

                        <input type="text" name="emergency_phone"
                               class="form-control" maxlength="20"
                               value="<?php echo htmlspecialchars($row['emergency_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                </div>
            </div>
        </div>

        <!-- BUTTONS -->
        <button type="submit" name="edit" class="btn save-btn">
            Save Changes
        </button>

        <a href="resident/profile.php" class="btn btn-secondary">
            Cancel
        </a>

    </form>

</div>

</body>
</html>
