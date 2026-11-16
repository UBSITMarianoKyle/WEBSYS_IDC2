<?php
session_start();

require_once __DIR__ . "/HealthSystem.php";

$healthSystem = new HealthSystem($pdo);

$error = '';

if (empty($_SESSION['register_token'])) {
    $_SESSION['register_token'] = bin2hex(random_bytes(32));
}

function old($field) {
    return htmlspecialchars(
        $_POST[$field] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

if (isset($_POST['register_btn'])) {

    if (!hash_equals(
        $_SESSION['register_token'],
        $_POST['register_token'] ?? ''
    )) {
        $error = "Invalid request. Please refresh the page.";
    } else {

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');

        $birth_date = $_POST['birth_date'] ?: null;
        $sex = $_POST['sex'] ?: null;
        $address = trim($_POST['address'] ?? '');
        $household_no = trim($_POST['household_no'] ?? '');
        $contact = trim($_POST['contact'] ?? '');

        $health_condition = trim($_POST['health_condition'] ?? '');
        $allergies = trim($_POST['allergies'] ?? '');
        $emergency_contact = trim($_POST['emergency_contact'] ?? '');
        $emergency_phone = trim($_POST['emergency_phone'] ?? '');

        if (
            $username === '' ||
            $firstname === '' ||
            $lastname === '' ||
            $password === ''
        ) {
            $error = "Please complete all required fields.";

        } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";

        } elseif (strlen($password) < 8) {
            $error = "Password must have at least 8 characters.";

        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";

        } elseif (
            $birth_date !== null &&
            (!DateTime::createFromFormat('!Y-m-d', $birth_date) ||
             $birth_date > date('Y-m-d'))
        ) {
            $error = "Please enter a valid birth date.";

        } elseif (
            $sex !== null &&
            !in_array($sex, ['Male', 'Female', 'Other'], true)
        ) {
            $error = "Please select a valid sex.";

        } else {

            try {
                $pdo->beginTransaction();

                // Check if account already exists
                $check = $pdo->prepare(
                    "SELECT user_id FROM users
                     WHERE username = :username"
                );

                $check->execute([
                    ':username' => $username
                ]);

                if ($check->fetch()) {
                    $pdo->rollBack();
                    $error = "This email is already registered.";

                } else {

                    // Create user account
                    $result = $healthSystem->register(
                        $username,
                        $password,
                        'resident'
                    );

                    if (!$result) {
                        throw new Exception("Account creation failed.");
                    }

                    // Get the newly created user ID
                    $user_id = $pdo->lastInsertId();

                    // Create resident profile
                    $resident_result = $healthSystem->add_resident(
                        $user_id,
                        $firstname,
                        $lastname,
                        $birth_date,
                        $sex,
                        $address ?: null,
                        $household_no ?: null,
                        $contact ?: null,
                        $health_condition ?: null,
                        $allergies ?: null,
                        $emergency_contact ?: null,
                        $emergency_phone ?: null
                    );

                    if (!$resident_result) {
                        throw new Exception("Resident profile creation failed.");
                    }

                    $pdo->commit();

                    unset($_SESSION['register_token']);

                    header("Location: login.php?registered=1");
                    exit;
                }

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log($e->getMessage());

                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Resident Registration | Barangay Tawang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="style.css">

    <style>
        .registration-card {
            max-width: 850px;
            margin: 40px auto;
            border-radius: 12px;
            overflow: hidden;
        }

        .registration-header {
            background-color: #123B70;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .registration-header img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
        }

        .form-section-title {
            color: #123B70;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 2px solid #FFEB00;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .register-btn {
            background-color: #123B70;
            border-color: #123B70;
            color: white;
        }

        .register-btn:hover {
            background-color: #0B284D;
            color: white;
        }
    </style>
</head>

<body class="bg-light">

<div class="container">

    <div class="card registration-card shadow-sm border-0">

        <div class="registration-header">

            <img src="brgylogo.jpg"
                 alt="Barangay Tawang Logo">

            <h3 class="mb-1">Resident Registration</h3>

            <p class="mb-0">
                Barangay Tawang Health System
            </p>

        </div>

        <div class="card-body p-4">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input type="hidden"
                       name="register_token"
                       value="<?= htmlspecialchars($_SESSION['register_token']) ?>">

                <!-- PERSONAL INFORMATION -->
                <h5 class="form-section-title">
                    Personal Information
                </h5>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <label class="form-label">First Name *</label>
                        <input type="text"
                               name="firstname"
                               class="form-control"
                               maxlength="100"
                               value="<?= old('firstname') ?>"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name *</label>
                        <input type="text"
                               name="lastname"
                               class="form-control"
                               maxlength="100"
                               value="<?= old('lastname') ?>"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Birth Date</label>
                        <input type="date"
                               name="birth_date"
                               class="form-control"
                               max="<?= date('Y-m-d') ?>"
                               value="<?= old('birth_date') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sex</label>
                        <select name="sex" class="form-select">
                            <option value="">Select Sex</option>
                            <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
                                <option value="<?= $option ?>"
                                    <?= old('sex') === $option ? 'selected' : '' ?>>
                                    <?= $option ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address"
                                  class="form-control"
                                  rows="2"><?= old('address') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Household Number</label>
                        <input type="text"
                               name="household_no"
                               class="form-control"
                               maxlength="30"
                               value="<?= old('household_no') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="tel"
                               name="contact"
                               class="form-control"
                               maxlength="20"
                               value="<?= old('contact') ?>">
                    </div>

                </div>

                <!-- HEALTH INFORMATION -->
                <h5 class="form-section-title">
                    Health Information
                </h5>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <label class="form-label">Health Condition</label>
                        <textarea name="health_condition"
                                  class="form-control"
                                  rows="2"><?= old('health_condition') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Allergies</label>
                        <textarea name="allergies"
                                  class="form-control"
                                  rows="2"><?= old('allergies') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text"
                               name="emergency_contact"
                               class="form-control"
                               maxlength="100"
                               value="<?= old('emergency_contact') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Emergency Contact Number</label>
                        <input type="tel"
                               name="emergency_phone"
                               class="form-control"
                               maxlength="20"
                               value="<?= old('emergency_phone') ?>">
                    </div>

                </div>

                <!-- ACCOUNT INFORMATION -->
                <h5 class="form-section-title">
                    Account Information
                </h5>

                <div class="row g-3 mb-4">

                    <div class="col-md-12">
                        <label class="form-label">Email / Username *</label>
                        <input type="email"
                               name="username"
                               class="form-control"
                               maxlength="255"
                               value="<?= old('username') ?>"
                               autocomplete="username"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               minlength="8"
                               autocomplete="new-password"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password"
                               name="confirm_password"
                               class="form-control"
                               minlength="8"
                               autocomplete="new-password"
                               required>
                    </div>

                </div>

                <button type="submit"
                        name="register_btn"
                        class="btn register-btn w-100 py-2">
                    Create Resident Account
                </button>

                <p class="text-center mt-3 mb-0">
                    Already have an account?
                    <a href="login.php">Log In</a>
                </p>

            </form>

        </div>
    </div>
</div>

</body>
</html>
