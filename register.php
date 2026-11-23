<?php
    session_start();
    if(empty($_SESSION["register_token"])) $_SESSION["register_token"]=bin2hex(random_bytes(32));
    require_once "HealthSystem.php";

    $obj = new HealthSystem($pdo);
    $error = "";

    if(isset($_POST['register_btn'])){

        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        $birth_date = $_POST['birth_date'];
        $sex = $_POST['sex'];
        $address = $_POST['address'];
        $household_no = $_POST['household_no'];
        $contact = $_POST['contact'];
        $health_condition = $_POST['health_condition'];
        $allergies = $_POST['allergies'];
        $emergency_contact = $_POST['emergency_contact'];
        $emergency_phone = $_POST['emergency_phone'];

        if(!hash_equals($_SESSION["register_token"], $_POST["register_token"] ?? "")){ $error="Invalid request."; }
        else if(trim($fname)==="" || trim($lname)==="" || !filter_var($username,FILTER_VALIDATE_EMAIL)){ $error="Enter a valid name and email."; }
        else if($password != $confirm_password){
            $error = "Passwords do not match.";
        }
        else if(strlen($password) < 8){
            $error = "Password must have at least 8 characters.";
        }
        else{

            $result = $obj->register($username, $password, 'resident');

            if($result){

                $user_id = $pdo->lastInsertId();

                $result = $obj->add_resident(
                    $user_id,
                    $fname,
                    $lname,
                    $birth_date ?: null,
                    $sex ?: null,
                    $address,
                    $household_no,
                    $contact,
                    $health_condition,
                    $allergies,
                    $emergency_contact,
                    $emergency_phone
                );

                if($result){
                    header("location:login.php?registered=1");
                    exit();
                }
                else{
                    $error = "Resident profile creation failed.";
                }
            }
            else{
                $error = "Registration failed. Username may already exist.";
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Resident Registration | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .registration-card {
            max-width: 1050px;
            width: 95%;
            margin: 30px auto;
            border-radius: 12px;
            overflow: hidden;
            background-color: white;
        }

        .registration-header {
            background-color: #123B70;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .registration-header img {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
        }

        /* Barangay logo watermark */
        .registration-card .card-body {
            padding: 35px !important;

            background-image:
                linear-gradient(
                    rgba(255, 255, 255, 0.88),
                    rgba(255, 255, 255, 0.88)
                ),
                url("brgylogo.png");

            background-repeat: no-repeat;
            background-position: center;
            background-size: 550px;
            background-color: white;
        }

        .form-section-title {
            color: #123B70;
            font-size: 19px;
            font-weight: 600;
            border-bottom: 2px solid #FFEB00;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-control,
        .form-select {
            padding: 12px;
            font-size: 15px;
        }

        .form-label {
            font-size: 15px;
        }

        .register-btn {
            background-color: #123B70;
            border-color: #123B70;
            color: white;
            padding: 12px;
            font-size: 16px;
        }

        .register-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        @media(max-width: 600px){
            .registration-card {
                width: 100%;
                margin: 20px auto;
            }

            .registration-card .card-body {
                padding: 20px !important;
                background-size: 300px;
            }
        }
    </style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><style>.password-wrap{position:relative}.password-wrap input{padding-right:48px}.password-eye{position:absolute;right:4px;top:50%;transform:translateY(-50%);width:44px;height:44px;border:0;background:transparent;color:#123B70;font-size:20px}</style></head>

<body>

<div class="container">

    <div class="card registration-card shadow-sm border-0">

        <div class="registration-header">

            <img src="brgylogo.png" alt="Barangay Tawang Logo">

            <h3 class="mb-1">Resident Registration</h3>

            <p class="mb-0">Barangay Tawang Health System</p>

        </div>

        <div class="card-body p-4">

            <?php if($error != ""): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <input type="hidden"
                       name="register_token"
                       value="<?php echo htmlspecialchars($_SESSION['register_token']); ?>">

                <!-- PERSONAL INFORMATION -->
                <h5 class="form-section-title">Personal Information</h5>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="firstname"
                               class="form-control" maxlength="100"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="lastname"
                               class="form-control" maxlength="100"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date"
                               class="form-control"
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sex</label>
                        <select name="sex" class="form-select">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address"
                                  class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Household Number</label>
                        <input type="text" name="household_no"
                               class="form-control" maxlength="30">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" name="contact"
                               class="form-control" maxlength="20">
                    </div>

                </div>

                <!-- HEALTH INFORMATION -->
                <h5 class="form-section-title">Health Information</h5>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <label class="form-label">Health Condition</label>
                        <textarea name="health_condition"
                                  class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Allergies</label>
                        <textarea name="allergies"
                                  class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact"
                               class="form-control" maxlength="100">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Number</label>
                        <input type="tel" name="emergency_phone"
                               class="form-control" maxlength="20">
                    </div>

                </div>

                <!-- ACCOUNT INFORMATION -->
                <h5 class="form-section-title">Account Information</h5>

                <div class="row g-3 mb-4">

                    <div class="col-md-12">
                        <label class="form-label">Email / Username *</label>
                        <input type="email" name="username"
                               class="form-control" maxlength="255"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <div class="password-wrap"><input type="password" id="password" name="password"
                               class="form-control" minlength="8"
                               required><button type="button" class="password-eye" aria-label="Show password" onclick="togglePassword(this)"><i class="bi bi-eye"></i></button></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <div class="password-wrap"><input type="password" id="confirm_password" name="confirm_password"
                               class="form-control" minlength="8"
                               required><button type="button" class="password-eye" aria-label="Show password" onclick="togglePassword(this)"><i class="bi bi-eye"></i></button></div>
                    </div>

                </div>

                <button type="submit" name="register_btn"
                        class="btn register-btn w-100">
                    Register
                </button>

                <p class="text-center mt-3 mb-0">
                    Already have an account?
                    <a href="login.php">Log In</a>
                </p>

            </form>

        </div>
    </div>
</div>

<script>function togglePassword(b){const p=b.parentElement.querySelector("input");p.type=p.type==="password"?"text":"password";b.querySelector("i").className=p.type==="text"?"bi bi-eye-slash":"bi bi-eye";b.setAttribute("aria-label",p.type==="text"?"Hide password":"Show password");}</script></body>
</html>
