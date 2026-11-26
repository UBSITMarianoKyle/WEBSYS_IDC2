<?php
session_start();
require_once __DIR__ . '/../HealthSystem.php';

if (!isset($_SESSION['ID']) || !isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'nurse') {
    header('Location: ../login.php');
    exit();
}

$obj = new HealthSystem($pdo);

$role = 'nurse';
$title = 'Nurse Dashboard';
$fullname = 'Nurse';
if (isset($_SESSION['USERNAME'])) {
    $fullname = $_SESSION['USERNAME'];
}

$year = (int)date('Y');

// Today's consultations
$consultations = $obj->report_todayconsultations();

// Vaccination records
$totalVaccinations = $obj->report_totalvaccinations();

$completedVaccinations = $obj->report_completedvaccinations();

$notCompleted = $totalVaccinations - $completedVaccinations;

$completionRate = 0;

if ($totalVaccinations > 0) {
    $completionRate = round(
        ($completedVaccinations / $totalVaccinations) * 100, 1
    );
}

// Maternal and child health
$pregnancies = $obj->report_pregnancies_count();

$prenatal = $obj->report_prenatal_visits_count();

$children = $obj->report_child_health_count();

$healthVisits = $obj->report_health_visits_count();

// Upcoming health schedules
$upcomingVaccinations = $obj->report_vaccinations();

$upcomingPrenatal = $obj->report_upcomingprenatal();

$upcomingChildVisits = $obj->report_upcomingchildvisits();

$upcomingAppointments = $obj->report_upcomingappointments();

// Monthly consultations


$monthly = [];
for ($i = 1; $i <= 12; $i++) { $monthly[$i] = 0; }

foreach ($obj->report_consultations_period_4($year) as $row) {
    $monthly[(int)$row['month_no']] = (int)$row['total'];
}

// Upcoming vaccination schedules
$vaccineSchedules = $obj->report_upcomingvaccines();

// Dashboard cards
$cards = [
    "Today's Consultations" => $consultations,
    'Completed Vaccinations' => $completedVaccinations,
    'Pregnancy Registrations' => $pregnancies,
    'Child Health Records' => $children
];

$maternal = [
    'Pregnancy Registrations' => $pregnancies,
    'Prenatal Visits' => $prenatal,
    'Child Health Records' => $children,
    'Health Visit Records' => $healthVisits
];

$schedules = [
    'Vaccination Schedules' => $upcomingVaccinations,
    'Prenatal Checkups' => $upcomingPrenatal,
    'Child Health Visits' => $upcomingChildVisits,
    'Approved Appointments' => $upcomingAppointments
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

    .coverage-ring {
        width: 160px;
        height: 160px;
        margin: 20px auto;
        border-radius: 50%;
        background: conic-gradient(
            #123B70 var(--rate),
            #E5C900 0
        );
        display: grid;
        place-items: center;
    }

    .coverage-ring strong {
        background: white;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 26px;
        color: #123B70;
    }

    .progress {
        height: 14px;
        margin-top: 8px;
    }

    .progress-bar {
        background: #123B70;
    }

    .chart-scroll {
        overflow-x: auto;
    }

    .month-chart {
        display: flex;
        align-items: end;
        gap: 8px;
        min-width: 480px;
    }

    .month-column {
        flex: 1;
        text-align: center;
        font-size: 12px;
    }

    .month-track {
        height: 150px;
        display: flex;
        align-items: end;
        justify-content: center;
        border-bottom: 1px solid #ddd;
    }

    .month-bar {
        background: #123B70;
        width: 65%;
        border-radius: 4px 4px 0 0;
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

    <h2>Nurse Dashboard</h2>

    <p class="mb-1">
        Patient Care and Clinical Health Monitoring
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

<!-- MONTHLY CONSULTATIONS -->

<div class="dashboard-card">

    <h5>Monthly Consultations (<?php echo  $year ?>)</h5>

    <p class="text-muted small">
        Total consultations recorded by month.
    </p>

    <div class="chart-scroll">

        <div class="month-chart">

            <?php foreach ($monthly as $month => $total): ?>

                <?php
                    $height = ($total /
                        max(1, max($monthly))) * 100;
                ?>

                <div class="month-column">

                    <strong><?php echo  $total ?></strong>

                    <div class="month-track">

                        <div class="month-bar"
                             style="height: <?php echo  $height ?>%">

                        </div>

                    </div>

                    <small>
                        <?php echo  date('M', mktime(0, 0, 0, $month, 1)) ?>
                    </small>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<div class="row g-3 mb-4">

    <!-- VACCINATION COMPLETION RATE -->

    <div class="col-lg-6">

        <div class="dashboard-card h-100 mb-0">

            <h5>Vaccination Completion Rate</h5>

            <div class="coverage-ring"
                 style="--rate: <?php echo  $completionRate ?>%;
                 <?php if ($totalVaccinations == 0) { echo 'background:#e9ecef;'; } ?>">

                <strong>

                    <?php if ($totalVaccinations > 0) { echo $completionRate . '%'; } else { echo 'N/A'; } ?>

                </strong>

            </div>

            <div class="d-flex justify-content-between mb-2">

                <span>Completed Vaccinations</span>

                <strong><?php echo  $completedVaccinations ?></strong>

            </div>

            <div class="d-flex justify-content-between mb-2">

                <span>Not Completed</span>

                <strong><?php echo  $notCompleted ?></strong>

            </div>

            <div class="d-flex justify-content-between">

                <span>Total Vaccination Records</span>

                <strong><?php echo  $totalVaccinations ?></strong>

            </div>

            <p class="text-muted small mt-3 mb-0">
                Completion rate is based on vaccination records,
                not the percentage of fully immunized residents.
            </p>

        </div>

    </div>

    <!-- MATERNAL AND CHILD HEALTH -->

    <div class="col-lg-6">

        <div class="dashboard-card h-100 mb-0">

            <h5>Maternal and Child Health</h5>

            <p class="text-muted small">
                Total recorded health services.
            </p>

            <?php foreach ($maternal as $label => $total): ?>

                <?php
                    $percent = ($total /
                        max(1, max($maternal))) * 100;
                ?>

                <div class="d-flex justify-content-between mt-3">

                    <span><?php echo  $label ?></span>

                    <strong><?php echo  $total ?></strong>

                </div>

                <div class="progress">

                    <div class="progress-bar"
                         style="width: <?php echo  $percent ?>%">

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<!-- UPCOMING HEALTH SCHEDULES -->

<div class="dashboard-card">

    <h5>Upcoming Health Schedules</h5>

    <div class="table-responsive">

        <table class="table table-bordered table-hover">

            <thead>

                <tr>
                    <th>Health Service</th>
                    <th>Scheduled Records</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($schedules as $label => $total): ?>

                    <tr>

                        <td><?php echo  $label ?></td>

                        <td><?php echo  $total ?></td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- UPCOMING VACCINATION DETAILS -->

<div class="dashboard-card">

    <h5>Upcoming Vaccination Details</h5>

    <div class="table-responsive">

        <table class="table table-bordered table-hover">

            <thead>

                <tr>
                    <th>Vaccine Type</th>
                    <th>Next Schedule</th>
                    <th>Total Records</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($vaccineSchedules as $row): ?>

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

                <?php if (empty($vaccineSchedules)): ?>

                    <tr>
                        <td colspan="3" class="text-center">
                            No upcoming vaccination schedules.
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
                Patient Consultations
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="vaccinations.php" class="action-btn">
                Vaccination Records
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="appointments.php" class="action-btn">
                Appointments
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
