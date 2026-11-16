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
    <a href="consultations.php">Consultations</a>
    <a href="vaccinations.php">Vaccinations</a>
    <a href="medicines.php" class="active">Medicine Inventory</a>
</div>

<!-- CONTENT -->
<div class="content">

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


    <div class="footer">
        Barangay Tawang Health System | Nurse Portal
    </div>

</div>

</body>
</html>
