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

    $medicines = $obj->show_medicines();
    $medicine_ids = array_column($medicines, 'medicine_id');

    // ADD NEW MEDICINE
    if(isset($_POST['add_medicine'])){

        $medicine_name = trim($_POST['medicine_name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $quantity = $_POST['quantity'] ?? '';
        $minimum_stock = $_POST['minimum_stock'] ?? '';
        $expiration_date = $_POST['expiration_date'] ?? '';

        if(!hash_equals($_SESSION['nurse_token'], $_POST['nurse_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if($medicine_name == "" || $unit == ""){
            $error = "Medicine name and unit are required.";
        }
        else if(strlen($medicine_name) > 100 || strlen($unit) > 30){
            $error = "Medicine name or unit is too long.";
        }
        else if(!ctype_digit($quantity) || !ctype_digit($minimum_stock)){
            $error = "Quantity and minimum stock must be whole numbers.";
        }
        else if(!DateTime::createFromFormat('!Y-m-d', $expiration_date) || $expiration_date <= date('Y-m-d')){
            $error = "Expiration date must be a future date.";
        }
        else{
            $result = $obj->add_medicine($medicine_name, $unit, (int)$quantity, (int)$minimum_stock, $expiration_date);

            if($result){
                header("location: medicines.php?added=1");
                exit();
            }
            else{
                $error = "Failed to add medicine.";
            }
        }
    }

    // STOCK IN / STOCK OUT
    if(isset($_POST['stock'])){

        $medicine_id = (int)($_POST['medicine_id'] ?? 0);
        $transaction_type = $_POST['transaction_type'] ?? '';
        $quantity = $_POST['stock_quantity'] ?? '';
        $resident_id = (int)($_POST['resident_id'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');

        if(!hash_equals($_SESSION['nurse_token'], $_POST['nurse_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if(!in_array($medicine_id, $medicine_ids)){
            $error = "Please select a valid medicine.";
        }
        else if(!in_array($transaction_type, ['stock_in', 'stock_out'], true)){
            $error = "Invalid transaction type.";
        }
        else if(!ctype_digit($quantity) || (int)$quantity < 1){
            $error = "Quantity must be at least 1.";
        }
        else if($resident_id != 0 && !in_array($resident_id, $resident_ids)){
            $error = "Invalid resident.";
        }
        else{
            // Only stock-out can be given to a resident
            if($transaction_type == 'stock_in'){
                $resident_id = 0;
            }

            $result = $obj->change_medicine_stock(
                $medicine_id,
                $resident_id ?: null,
                $_SESSION['ID'],
                $transaction_type,
                (int)$quantity,
                $remarks
            );

            if($result){
                header("location: medicines.php?stocked=1");
                exit();
            }
            else{
                $error = "Failed to update stock. Check that there is enough stock.";
            }
        }
    }

    // Reload after changes
    $medicines = $obj->show_medicines();
    $transactions = array_slice($obj->show_medicine_transactions(), 0, 20);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Medicine Inventory | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
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

        .main-content h2,
        .main-content h3 {
            color: #123B70;
        }

        .main-content h2 {
            font-size: 26px;
            font-weight: bold;
        }

        .welcome-header {
            background-color: #123B70;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .welcome-header h2 {
            color: white;
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

        /* CARDS */
        .box {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px #00000012;
        }

        .box:has(.data-table) {
            overflow-x: auto;
        }

        .box h3 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

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

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #123B70;
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
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #f1f4f8;
        }

        .data-table form {
            display: inline-block;
            margin: 0 4px 4px 0;
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

            .form-table th {
                width: auto;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center gap-2">

        <a href="index.php"
           class="navbar-brand d-flex align-items-center gap-2">

            <img src="../logo/brgylogo.jpg"
                 class="barangay-logo"
                 alt="Barangay Logo">

            <span>Barangay Tawang Health System</span>
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="d-none d-md-inline">
                <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <a href="../logout.php" class="btn logout-btn btn-sm">
                Logout
            </a>

        </div>
    </div>
</nav>

<!-- PAGE LAYOUT -->
<div class="container-fluid">
    <div class="row g-0">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar">

            <h5>Nurse Portal</h5>

            <a href="index.php">
                Dashboard
            </a>

            <a href="appointments.php">
                Appointments
            </a>

            <a href="consultations.php">
                Consultations
            </a>

            <a href="vaccinations.php">
                Vaccinations
            </a>

            <a href="medicines.php" class="active">
                Medicine Inventory
            </a>

        </div>

        <!-- MAIN CONTENT -->
        <main class="col-md-9 col-lg-10 main-content">


            <h2>Medicine Inventory</h2>
            <p>Monitor stock, record stock-in and stock-out, and track medicine distribution.</p>

            <?php if(isset($_GET['added'])): ?>
                <p class="alert alert-success">Medicine added successfully.</p>
            <?php endif; ?>
            <?php if(isset($_GET['stocked'])): ?>
                <p class="alert alert-success">Stock updated successfully.</p>
            <?php endif; ?>
            <?php if($error != ""): ?>
                <p class="alert alert-danger">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <!-- STOCK IN / OUT -->
            <div class="box">

                <h3>Stock-In / Stock-Out</h3>

                <form method="post">

                    <input type="hidden" name="nurse_token" value="<?php echo $_SESSION['nurse_token']; ?>">

                    <table class="form-table">

                        <tr>
                            <th>Medicine</th>
                            <td>
                                <select name="medicine_id" class="form-select" required>
                                    <option value="" disabled <?php if(empty($_POST['medicine_id'])) echo 'selected'; ?>>Select Medicine</option>
                                    <?php foreach($medicines as $m): ?>
                                        <option value="<?php echo (int)$m['medicine_id']; ?>"
                                            <?php if(($_POST['medicine_id'] ?? '') == $m['medicine_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($m['medicine_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            (<?php echo (int)$m['quantity']; ?> <?php echo htmlspecialchars($m['unit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th>Transaction</th>
                            <td>
                                <select name="transaction_type" class="form-select" required>
                                    <option value="stock_in" <?php if(($_POST['transaction_type'] ?? '') == 'stock_in') echo 'selected'; ?>>Stock-In (add stock)</option>
                                    <option value="stock_out" <?php if(($_POST['transaction_type'] ?? 'stock_out') == 'stock_out') echo 'selected'; ?>>Stock-Out (distribute)</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>
                                <input type="number" name="stock_quantity" class="form-control" min="1"
                                       value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th>Given to Resident (stock-out only)</th>
                            <td>
                                <select name="resident_id" class="form-select">
                                    <option value="">None</option>
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
                            <th>Remarks</th>
                            <td>
                                <input type="text" name="remarks" class="form-control" maxlength="255"
                                       value="<?php echo htmlspecialchars($_POST['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2">
                                <button type="submit" name="stock" class="btn submit-btn">Save Transaction</button>
                            </td>
                        </tr>

                    </table>

                </form>

            </div>

            <!-- AVAILABLE MEDICINES -->
            <div class="box">

                <h3>Available Medicines</h3>

                <table class="data-table">

                    <tr>
                        <th>Medicine</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Minimum Stock</th>
                        <th>Expiration</th>
                        <th>Status</th>
                    </tr>

                    <?php if(count($medicines) > 0): ?>

                        <?php foreach($medicines as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['medicine_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$row['quantity']; ?></td>
                                <td><?php echo (int)$row['minimum_stock']; ?></td>
                                <td>
                                    <?php
                                        if(!empty($row['expiration_date'])){
                                            echo date("M d, Y", strtotime($row['expiration_date']));
                                        }
                                        else{
                                            echo "-";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if(!empty($row['expiration_date']) && $row['expiration_date'] < date('Y-m-d')){
                                            echo 'Expired. ';
                                        }

                                        if($row['quantity'] <= $row['minimum_stock']){
                                            echo 'Low Stock';
                                        }
                                        else{
                                            echo 'In Stock';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="empty">No medicines found.</td>
                        </tr>

                    <?php endif; ?>

                </table>

            </div>

            <!-- ADD NEW MEDICINE -->
            <div class="box">

                <h3>Add New Medicine</h3>

                <form method="post">

                    <input type="hidden" name="nurse_token" value="<?php echo $_SESSION['nurse_token']; ?>">

                    <table class="form-table">

                        <tr>
                            <th>Medicine Name</th>
                            <td>
                                <input type="text" name="medicine_name" class="form-control" maxlength="100"
                                       value="<?php echo htmlspecialchars($_POST['medicine_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th>Unit</th>
                            <td>
                                <input type="text" name="unit" class="form-control" maxlength="30"
                                       placeholder="tablet, bottle"
                                       value="<?php echo htmlspecialchars($_POST['unit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>
                                <input type="number" name="quantity" class="form-control" min="0"
                                       value="<?php echo htmlspecialchars($_POST['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th>Minimum Stock</th>
                            <td>
                                <input type="number" name="minimum_stock" class="form-control" min="0"
                                       value="<?php echo htmlspecialchars($_POST['minimum_stock'] ?? '10', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th>Expiration Date</th>
                            <td>
                                <input type="date" name="expiration_date" class="form-control"
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       value="<?php echo htmlspecialchars($_POST['expiration_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2">
                                <button type="submit" name="add_medicine" class="btn submit-btn">Add Medicine</button>
                            </td>
                        </tr>

                    </table>

                </form>

            </div>

            <!-- DISTRIBUTION RECORDS -->
            <div class="box">

                <h3>Recent Stock Transactions and Distribution Records</h3>

                <table class="data-table">

                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Resident</th>
                        <th>Remarks</th>
                    </tr>

                    <?php if(count($transactions) > 0): ?>

                        <?php foreach($transactions as $row): ?>
                            <tr>
                                <td><?php echo date("M d, Y - h:i A", strtotime($row['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['medicine_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $row['transaction_type'] == 'stock_in' ? 'Stock-In' : 'Stock-Out'; ?></td>
                                <td><?php echo (int)$row['quantity']; ?></td>
                                <td>
                                    <?php
                                        if(!empty($row['first_name'])){
                                            echo htmlspecialchars($row['first_name'] . " " . $row['last_name'], ENT_QUOTES, 'UTF-8');
                                        }
                                        else{
                                            echo "-";
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="empty">No transactions yet.</td>
                        </tr>

                    <?php endif; ?>

                </table>

            </div>

            <!-- FOOTER -->
            <div class="dashboard-footer">
                Barangay Tawang Health System | Nurse Portal
            </div>

        </main>

    </div>
</div>

</body>
</html>
