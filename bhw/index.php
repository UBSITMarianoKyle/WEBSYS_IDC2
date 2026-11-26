
<?php
session_start();
require_once __DIR__ . '/../HealthSystem.php';

if (!isset($_SESSION['ID']) || !isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'bhw') {
    header('Location: ../login.php');
    exit();
}

$obj = new HealthSystem($pdo);

$role = 'bhw';
$title = 'BHW Dashboard';
$fullname = 'Barangay Health Worker';
if (isset($_SESSION['USERNAME'])) {
    $fullname = $_SESSION['USERNAME'];
}

// Registered residents
$residents = $obj->report_residentcount();

// Today's consultations
$todayConsultations = $obj->report_todayconsultations();

// Pending appointments
$pending = $obj->report_pending();

// Upcoming health visits
$visits = $obj->report_visits();

// Upcoming vaccinations
$vaccinations = $obj->report_vaccinations();

// Low-stock medicines
$lowStock = $obj->report_lowstock();

// Weekly consultations
$weekly = [];

for ($i = 6; $i >= 0; $i--) {

    $date = date('Y-m-d', strtotime("-$i days"));

    

    $weekly[$date] = (int)$obj->report_consultations_period_3($date);
}

// Appointment status
$appointments = $obj->report_appointments_2();

$appointmentTotal = 0;
foreach ($appointments as $row) { $appointmentTotal += $row['total']; }

// Upcoming vaccination schedules
$upcomingVaccines = $obj->report_upcomingvaccines();

// Upcoming health visit schedules
$upcomingVisits = $obj->report_upcomingvisits();

// Summary cards
$cards = [
    'Registered Residents' => $residents,
    "Today's Consultations" => $todayConsultations,
    'Pending Appointments' => $pending,
    'Upcoming Health Visits' => $visits
];

require __DIR__ . '/module_shell.php';
?>

<style>

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px #00000012;
        height: 100%;
    }

    .stat-number {
        color: #123B70;
        font-size: 30px;
        font-weight: bold;
    }

    .dashboard-card {
        background: white;
        padding: 22px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px #00000012;
    }

    .dashboard-card h5 {
        color: #123B70;
        font-weight: bold;
        margin-bottom: 18px;
    }

    .chart-scroll {
        overflow-x: auto;
    }

    .weekly-chart {
        display: flex;
        align-items: end;
        gap: 12px;
        min-width: 350px;
    }

    .chart-column {
        flex: 1;
        text-align: center;
        font-size: 12px;
    }

    .chart-track {
        height: 150px;
        display: flex;
        align-items: end;
        justify-content: center;
        border-bottom: 1px solid #ddd;
    }

    .chart-bar {
        width: 65%;
        background: #123B70;
        border-radius: 5px 5px 0 0;
    }

    .progress {
        height: 14px;
        margin-top: 8px;
    }

    .progress-bar {
        background: #123B70;
    }

    .action-btn {
        background: #123B70;
        color: white;
        padding: 12px;
        display: block;
        text-decoration: none;
        text-align: center;
        border-radius: 6px;
        height: 100%;
    }

    .action-btn:hover {
        background: #244F83;
        color: white;
    }

    .task-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    @media(max-width: 767px) {

        .stat-card,
        .dashboard-card {
            padding: 15px;
        }

        .stat-number {
            font-size: 25px;
        }

    }

</style>

<!-- WELCOME HEADER -->

<div class="welcome-header">

    <h2>BHW Dashboard</h2>

    <p class="mb-1">
        Community Health Monitoring
    </p>

    <small>
        Welcome, <?php echo  htmlspecialchars($fullname) ?>
    </small>

</div>

<!-- SUMMARY CARDS -->

<div class="row g-3 mb-4">

    <?php foreach ($cards as $label => $total): ?>

        <div class="col-6 col-xl-3">

            <div class="stat-card">

                <small><?php echo  htmlspecialchars($label) ?></small>

                <div class="stat-number">
                    <?php echo  number_format($total) ?>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<!-- WEEKLY CONSULTATION CHART -->

<div class="dashboard-card">

    <h5>Weekly Consultations</h5>

    <p class="text-muted small">
        Patient consultations during the last seven days.
    </p>

    <div class="chart-scroll">

        <div class="weekly-chart">

            <?php foreach ($weekly as $date => $total): ?>

                <?php
                    $height = ($total /
                        max(1, max($weekly))) * 100;
                ?>

                <div class="chart-column">

                    <strong><?php echo  $total ?></strong>

                    <div class="chart-track">

                        <div class="chart-bar"
                             style="height: <?php echo  $height ?>%">

                        </div>

                    </div>

                    <small>
                        <?php echo  date('D', strtotime($date)) ?>
                        <br>
                        <?php echo  date('M d', strtotime($date)) ?>
                    </small>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<div class="row g-3 mb-4">

    <!-- APPOINTMENT STATUS -->

    <div class="col-lg-6">

        <div class="dashboard-card h-100 mb-0">

            <h5>Appointment Status</h5>

            <p class="text-muted small">
                Total Appointments: <?php echo  $appointmentTotal ?>
            </p>

            <?php foreach ($appointments as $row): ?>

                <?php
                    $percent = round(
                        ($row['total'] /
                        max(1, $appointmentTotal)) * 100, 1
                    );

                    $color = '#123B70';

                    if ($row['status'] == 'pending') {
                        $color = '#E5C900';
                    } elseif ($row['status'] == 'completed') {
                        $color = '#198754';
                    } elseif ($row['status'] == 'rejected') {
                        $color = '#dc3545';
                    }
                ?>

                <div class="d-flex justify-content-between mt-3">

                    <span>
                        <?php echo  htmlspecialchars(ucfirst($row['status'])) ?>
                    </span>

                    <span>
                        <?php echo  $row['total'] ?> (<?php echo  $percent ?>%)
                    </span>

                </div>

                <div class="progress">

                    <div class="progress-bar"
                         style="width: <?php echo  $percent ?>%;
                         background: <?php echo  $color ?>">

                    </div>

                </div>

            <?php endforeach; ?>

            <?php if (empty($appointments)): ?>

                <p>No appointment records available.</p>

            <?php endif; ?>

        </div>

    </div>

    <!-- TASKS REQUIRING ATTENTION -->

    <div class="col-lg-6">

        <div class="dashboard-card h-100 mb-0">

            <h5>Tasks Requiring Attention</h5>

            <div class="task-row">
                <span>Pending Appointments</span>
                <strong><?php echo  $pending ?></strong>
            </div>

            <div class="task-row">
                <span>Upcoming Vaccinations</span>
                <strong><?php echo  $vaccinations ?></strong>
            </div>

            <div class="task-row">
                <span>Scheduled Health Visits</span>
                <strong><?php echo  $visits ?></strong>
            </div>

            <div class="task-row">
                <span>Low-Stock Medicines</span>
                <strong class="text-danger"><?php echo  $lowStock ?></strong>
            </div>

        </div>

    </div>

</div>

<!-- UPCOMING VACCINATIONS -->

<div class="dashboard-card">

    <h5>Upcoming Vaccination Schedules</h5>

    <div class="table-responsive">

        <table class="table table-bordered table-hover">

            <thead>

                <tr>
                    <th>Vaccine</th>
                    <th>Schedule</th>
                    <th>Records</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($upcomingVaccines as $row): ?>

                    <tr>

                        <td>
                            <?php echo  htmlspecialchars((string)$row['vaccine_name']) ?>
                        </td>

                        <td>
                            <?php echo  htmlspecialchars($row['next_schedule']) ?>
                        </td>

                        <td><?php echo  $row['total'] ?></td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($upcomingVaccines)): ?>

                    <tr>
                        <td colspan="3" class="text-center">
                            No upcoming vaccinations.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- UPCOMING HEALTH VISITS -->

<div class="dashboard-card">

    <h5>Scheduled Health Visits</h5>

    <div class="table-responsive">

        <table class="table table-bordered table-hover">

            <thead>

                <tr>
                    <th>Visit Type</th>
                    <th>Scheduled Date</th>
                    <th>Total</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($upcomingVisits as $row): ?>

                    <tr>

                        <td>
                            <?php echo  htmlspecialchars((string)$row['visit_type']) ?>
                        </td>

                        <td>
                            <?php echo  htmlspecialchars($row['scheduled_date']) ?>
                        </td>

                        <td><?php echo  $row['total'] ?></td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($upcomingVisits)): ?>

                    <tr>
                        <td colspan="3" class="text-center">
                            No upcoming health visits.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- QUICK ACTIONS -->

<div class="dashboard-card">

    <h5>Quick Actions</h5>

    <div class="row g-3">

        <div class="col-6 col-lg-3">
            <a href="consultations.php" class="action-btn">
                Add Consultation
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="appointments.php" class="action-btn">
                Manage Appointments
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="medical_supplies.php" class="action-btn">
                Medical Supplies
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="health_visits.php" class="action-btn">
                Health Visits
            </a>
        </div>

    </div>

</div>

</main>
</div>
</div>

</body>
</html>
