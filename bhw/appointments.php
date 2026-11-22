<?php
    session_start();

    require_once __DIR__ . "/../HealthSystem.php";

    if(!isset($_SESSION['ID']) || ($_SESSION['ROLE'] ?? '') != 'bhw'){
        header("location: ../login.php");
        exit();
    }

    $obj = new HealthSystem($pdo);

    $fullname = $_SESSION['USERNAME'] ?? 'BHW';
    $error = "";

    if(!isset($_SESSION['bhw_token'])){
        $_SESSION['bhw_token'] = bin2hex(random_bytes(32));
    }

    if(isset($_POST['update_status'])){

        $appointment_id = (int)($_POST['appointment_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';

        $found = $obj->show_updated_appointment($appointment_id);
        $current = $found[0]['status'] ?? '';

        // pending -> approved / rejected, approved -> completed
        $allowed = [
            'pending'  => ['approved', 'rejected'],
            'approved' => ['completed']
        ];

        if(!hash_equals($_SESSION['bhw_token'], $_POST['bhw_token'] ?? '')){
            $error = "Invalid request.";
        }
        else if(!$found){
            $error = "Appointment not found.";
        }
        else if(!in_array($new_status, $allowed[$current] ?? [], true)){
            $error = "That status change is not allowed.";
        }
        else{
            $obj->update_appointment($appointment_id, $new_status, $_SESSION['ID']);

            header("location: appointments.php?updated=1");
            exit();
        }
    }

    $appointments = $obj->show_appointments();


    $filter = isset($_POST['filter']) ? ($_POST['status'] ?? 'all') : 'all';

    if(!in_array($filter, ['all', 'pending', 'approved', 'rejected', 'completed'], true)){
        $filter = 'all';
    }
$role='bhw'; $title='Appointments'; require __DIR__.'/../module_header.php'; ?>




            <h2>Appointment Management</h2>
            <p>Approve or reject appointment requests and view appointment schedules.</p>

            <?php if(isset($_GET['updated'])): ?>
                <p class="alert alert-success">Appointment updated successfully.</p>
            <?php endif; ?>
            <?php if($error != ""): ?>
                <p class="alert alert-danger">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <div class="box">

                <h3>Appointment Requests</h3>

                <!-- FILTER -->
                <form method="post">

                    <table class="form-table">
                        <tr>
                            <th>Show Status</th>
                            <td>
                                <select name="status" class="form-select">
                                    <?php foreach(['all', 'pending', 'approved', 'rejected', 'completed'] as $s): ?>
                                        <option value="<?php echo $s; ?>"
                                            <?php if($filter == $s) echo 'selected'; ?>>
                                            <?php echo ucfirst($s); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <button type="submit" name="filter" class="btn submit-btn">Filter</button>
                            </td>
                        </tr>
                    </table>

                </form>

                <!-- APPOINTMENT TABLE -->
                <table class="data-table">

                    <tr>
                        <th>ID</th>
                        <th>Resident</th>
                        <th>Service Type</th>
                        <th>Date and Time</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                    <?php $shown = 0; ?>

                    <?php foreach($appointments as $row): ?>

                        <?php
                            if($filter != 'all' && $row['status'] != $filter){
                                continue;
                            }
                            $shown++;
                        ?>

                        <tr>
                            <td><?php echo (int)$row['appointment_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo date("M d, Y - h:i A", strtotime($row['appointment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['status']), ENT_QUOTES, 'UTF-8'); ?></td>

                            <td>
                                <?php if($row['status'] == 'pending'): ?>

                                    <form method="post">
                                        <input type="hidden" name="bhw_token" value="<?php echo $_SESSION['bhw_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                        <input type="hidden" name="new_status" value="approved">
                                        <button type="submit" name="update_status" class="btn btn-success btn-sm">Approve</button>
                                    </form>

                                    <form method="post">
                                        <input type="hidden" name="bhw_token" value="<?php echo $_SESSION['bhw_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                        <input type="hidden" name="new_status" value="rejected">
                                        <button type="submit" name="update_status" class="btn btn-danger btn-sm">Reject</button>
                                    </form>

                                <?php elseif($row['status'] == 'approved'): ?>

                                    <form method="post">
                                        <input type="hidden" name="bhw_token" value="<?php echo $_SESSION['bhw_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                        <input type="hidden" name="new_status" value="completed">
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Mark Completed</button>
                                    </form>

                                <?php else: ?>

                                    -

                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <?php if($shown == 0): ?>
                        <tr>
                            <td colspan="7" class="empty">No appointments found.</td>
                        </tr>
                    <?php endif; ?>

                </table>

            </div>

            <!-- FOOTER -->
            <div class="dashboard-footer">
                Barangay Tawang Health System | BHW Portal
            </div>

        <?php require __DIR__.'/../module_footer.php'; ?>