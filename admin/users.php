
<?php
session_start();
require_once __DIR__ . '/../HealthSystem.php';

// Only the administrator can open this page.
if (!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'admin') {
    header('Location: ../login.php');
    exit();
}

$obj = new HealthSystem($pdo);
$fullname = $_SESSION['USERNAME'] ?? 'Administrator';

$message = '';
$error = '';

if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!hash_equals($_SESSION['admin_token'], $_POST['token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        try {
            if (isset($_POST['add_user'])) {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? '';

                if (
                    !filter_var($username, FILTER_VALIDATE_EMAIL) ||
                    strlen($password) < 8 ||
                    !in_array($role, ['admin', 'bhw', 'nurse', 'mho', 'resident'])
                ) {
                    $error = 'Enter a valid email, password (8+ characters), and role.';
                } else {
                    $check = $pdo->prepare('SELECT user_id FROM users WHERE username = ?');
                    $check->execute([$username]);

                    if ($check->fetch()) {
                        $error = 'Username already exists.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $save = $pdo->prepare('INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)');
                        $save->execute([$username, $hash, $role, 'active']);
                        $message = 'User added successfully.';
                    }
                }
            }

            if (isset($_POST['update_status'])) {
                $id = (int)($_POST['user_id'] ?? 0);
                $status = $_POST['status'] ?? '';

                if ($id <= 0 || !in_array($status, ['active', 'inactive'])) {
                    $error = 'Invalid user or status.';
                } elseif ($id == (int)$_SESSION['ID'] && $status == 'inactive') {
                    $error = 'You cannot deactivate your own account.';
                } else {
                    $save = $pdo->prepare('UPDATE users SET status = ? WHERE user_id = ?');
                    $save->execute([$status, $id]);
                    $message = 'Status updated.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Could not save changes. Please check the data.';
        }
    }
}

$users = $pdo->query('SELECT user_id, username, role, status FROM users ORDER BY user_id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users | Barangay Tawang Health System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        /* NAVBAR */
        .navbar {
            background-color: #FFEB00;
            border-bottom: 3px solid #123B70;
            padding: 10px 20px;
        }

        .navbar-brand {
            color: #123B70;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
        }

        .barangay-logo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .logout-btn {
            border: 1px solid #123B70;
            color: #123B70;
        }

        .logout-btn:hover {
            background-color: #123B70;
            color: white;
        }

        /* SIDEBAR */
        .sidebar {
            background-color: #123B70;
            min-height: calc(100vh - 73px);
            padding: 20px 15px;
        }

        .sidebar h5 {
            color: white;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 15px;
        }

        .sidebar a:hover {
            background-color: #244F83;
        }

        .sidebar a.active {
            background-color: #FFEB00;
            color: #123B70;
            font-weight: bold;
        }

        /* MAIN CONTENT */
        .main-content {
            padding: 25px;
        }

        .welcome-header {
            background-color: #123B70;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .welcome-header h2 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .welcome-header p {
            margin-bottom: 0;
            font-size: 15px;
        }

        .section-title {
            color: #123B70;
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .section-description {
            color: #6c757d;
            margin-bottom: 20px;
        }

        /* SERVICE CARDS */
        .service-card {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            height: 100%;
            transition: 0.2s;
        }

        .service-card:hover {
            border-color: #123B70;
        }

        .service-card .card-body {
            padding: 22px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .service-card h5 {
            color: #123B70;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .service-card p {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .service-btn {
            background-color: #123B70;
            color: white;
            padding: 10px 15px;
            margin-top: auto;
            border-radius: 6px;
        }

        .service-btn:hover {
            background-color: #0B284D;
            color: white;
        }

        /* FOOTER */
        .dashboard-footer {
            margin-top: 35px;
            padding: 15px 0;
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #dee2e6;
        }

        /* MOBILE */
        @media(max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 20px 15px;
            }

            .welcome-header {
                padding: 20px;
            }

            .welcome-header h2 {
                font-size: 23px;
            }

            .navbar-brand {
                font-size: 14px;
            }
        }

        .card-box {
            background: white;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px #00000012;
        }

        .card-box h5 {
            color: #123B70;
            font-weight: bold;
        }

        .table thead th {
            background: #123B70;
            color: white;
        }

        .btn-main {
            background: #123B70;
            color: white;
        }

        .btn-main:hover {
            background: #244F83;
            color: white;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #123B70;
        }

        @media(max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Same navbar as Resident -->
    <nav class="navbar"><button type="button" class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" onclick="document.body.classList.toggle('menu-open');this.setAttribute('aria-expanded',document.body.classList.contains('menu-open'))">☰</button>
        <div class="container-fluid d-flex justify-content-between align-items-center gap-2">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
                <img src="../brgylogo.png" class="barangay-logo" alt="Barangay Logo">
                <span>Barangay Tawang Health System</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($fullname); ?></span>
                <a href="../logout.php" class="btn logout-btn btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row g-0">

            <!-- Same sidebar style as Resident -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h5>Admin Portal</h5>

                <a href="index.php">Dashboard</a>
                <a href="users.php" class="active">Users</a>
                <a href="residents.php">Residents</a>
                <a href="medicines.php">Medicines</a>
                <a href="reports.php">Reports</a>
            </div>

            <main class="col-md-9 col-lg-10 main-content">
                <div class="page-header mb-4">
                    <h2>Manage Users</h2>
                </div>

                <?php if ($message != '') { ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } ?>

                <?php if ($error != '') { ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php } ?>

                <div class="card-box">
                    <h5>Add User</h5>

                    <form method="post" class="row g-3">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['admin_token']); ?>">

                        <div class="col-md-4">
                            <label class="form-label">Email / Username</label>
                            <input type="email" name="username" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" minlength="8" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="resident">Resident</option>
                                <option value="bhw">BHW</option>
                                <option value="nurse">Nurse</option>
                                <option value="mho">MHO</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="add_user" class="btn btn-main w-100">Add User</button>
                        </div>
                    </form>
                </div>

                <div class="card-box">
                    <h5>User Accounts</h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($users as $user) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars($user['status']); ?></td>

                                        <td>
                                            <form method="post" class="d-flex gap-2">
                                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['admin_token']); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">

                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="active" <?php if ($user['status'] == 'active') echo 'selected'; ?>>Active</option>
                                                    <option value="inactive" <?php if ($user['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                                                </select>

                                                <button type="submit" name="update_status" class="btn btn-main btn-sm">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
