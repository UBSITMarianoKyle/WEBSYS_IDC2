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

$residentCount = (int)$pdo->query('SELECT COUNT(*) FROM residents')->fetchColumn();
$consultationCount = (int)$pdo->query('SELECT COUNT(*) FROM consultations')->fetchColumn();
$vaccinationCount = (int)$pdo->query('SELECT COUNT(*) FROM vaccinations')->fetchColumn();
$pregnancyCount = (int)$pdo->query('SELECT COUNT(*) FROM pregnancies')->fetchColumn();
$childCount = (int)$pdo->query('SELECT COUNT(*) FROM child_health')->fetchColumn();
$medicineCount = (int)$pdo->query('SELECT COUNT(*) FROM medicines')->fetchColumn();
$lowStock = $pdo->query('SELECT medicine_name, quantity, minimum_stock FROM medicines WHERE quantity <= minimum_stock')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health Reports | Barangay Tawang Health System</title>
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
        @media(max-width: 767px){

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
    
.card-box { background:white; border-radius:10px; padding:22px; margin-bottom:22px; box-shadow:0 2px 8px #00000012; }
.card-box h5 { color:#123B70; font-weight:bold; }
.table thead th { background:#123B70; color:white; }
.btn-main {background:#123B70; color:white;} .btn-main:hover {background:#244F83; color:white;}
.stat-number {font-size:32px; font-weight:bold; color:#123B70;}
@media(max-width:767px){.sidebar{min-height:auto}.main-content{padding:16px}}
</style>
</head>
<body>
    <!-- Same navbar as Resident -->
    <nav class="navbar">
        <div class="container-fluid d-flex justify-content-between align-items-center gap-2">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
                <img src="../brgylogo.jpg" class="barangay-logo" alt="Barangay Logo">
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
<a href="users.php">Users</a>
<a href="residents.php">Residents</a>
<a href="medicines.php">Medicines</a>
<a href="reports.php" class="active">Reports</a>
            </div>
            <main class="col-md-9 col-lg-10 main-content">
                <div class="page-header mb-4"><h2>Health Reports</h2></div>
                <div class="row g-3">
<div class="col-md-4"><div class="card-box"><h5>Residents</h5><div class="stat-number"><?php echo $residentCount; ?></div></div></div>
<div class="col-md-4"><div class="card-box"><h5>Consultations</h5><div class="stat-number"><?php echo $consultationCount; ?></div></div></div>
<div class="col-md-4"><div class="card-box"><h5>Vaccinations</h5><div class="stat-number"><?php echo $vaccinationCount; ?></div></div></div>
<div class="col-md-4"><div class="card-box"><h5>Pregnancies</h5><div class="stat-number"><?php echo $pregnancyCount; ?></div></div></div>
<div class="col-md-4"><div class="card-box"><h5>Child Health Records</h5><div class="stat-number"><?php echo $childCount; ?></div></div></div>
<div class="col-md-4"><div class="card-box"><h5>Medicines</h5><div class="stat-number"><?php echo $medicineCount; ?></div></div></div>
</div><div class="card-box mt-3"><h5>Low Stock Medicines</h5><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Medicine</th><th>Quantity</th><th>Minimum Stock</th></tr></thead><tbody>
<?php foreach ($lowStock as $medicine) { ?><tr><td><?php echo htmlspecialchars($medicine['medicine_name']); ?></td><td><?php echo htmlspecialchars($medicine['quantity']); ?></td><td><?php echo htmlspecialchars($medicine['minimum_stock']); ?></td></tr><?php } ?></tbody></table>
<?php if (empty($lowStock)) { ?><p>No low stock medicines.</p><?php } ?></div></div>
            </main>
        </div>
    </div>
</body>
</html>
