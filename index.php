
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Welcome | Barangay Tawang Health System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  	<link rel="stylesheet" href="style.css">
</head>

<body>

<!-- NAVIGATION BAR -->
<nav class="navbar navbar-dark bg-success">
    <div class="container d-flex justify-content-between align-items-center">

        <a class="navbar-brand fw-semibold d-flex align-items-center gap-2"
           href="index.php">

            <img src="logo/brgylogo.jpg"
                 alt="Barangay Tawang Logo"
                 class="barangay-logo">

            <span>Barangay Tawang Health System</span>
        </a>

        <div class="d-flex gap-2">
            <a href="login.php"
               class="btn btn-outline-light btn-sm px-3">
                Login
            </a>

            <a href="register.php"
               class="btn btn-light text-success btn-sm px-3">
                Register
            </a>
        </div>

    </div>
</nav>

<main>

    <!-- WELCOME SECTION -->
    <section class="hero-section bg-success text-white">
        <div class="container">

            <h1 class="mb-3">
                Health Services and Community Health Monitoring
            </h1>

            <p class="mb-3">
                Keep resident health records, vaccinations,
                maternal and child care, medicine stock,
                and clinic appointments in one place.
            </p>

            <div class="d-flex flex-wrap gap-2">

                <a href="login.php"
                   class="btn btn-light text-success px-4">
                    Login
                </a>

                <a href="register.php"
                   class="btn btn-outline-light px-4">
                    Register as a Resident
                </a>

            </div>

        </div>
    </section>


    <!-- HEALTH SERVICES -->
    <section class="container py-5">

        <div class="mb-4">
            <h2 class="section-title">Health Services</h2>

            <p class="section-description mb-0">
                Explore the available health services and
                features of the Barangay Tawang Health System.
            </p>
        </div>

        <div class="row g-3">

            <!-- RESIDENT REGISTRATION -->
            <div class="col-md-6 col-lg-4">
                <article class="card service-card shadow-sm">
                    <div class="card-body">

                        <h3>Resident Registration</h3>

                        <p>
                            Manage household and resident records,
                            health profiles, and emergency contacts.
                        </p>

                    </div>
                </article>
            </div>


            <!-- CONSULTATIONS -->
            <div class="col-md-6 col-lg-4">
                <article class="card service-card shadow-sm">
                    <div class="card-body">

                        <h3>Consultations</h3>

                        <p>
                            Record symptoms, health worker assessments,
                            and consultation history.
                        </p>

                    </div>
                </article>
            </div>


            <!-- VACCINATION -->
            <div class="col-md-6 col-lg-4">
                <article class="card service-card shadow-sm">
                    <div class="card-body">

                        <h3>Vaccination</h3>

                        <p>
                            Track vaccination records, upcoming
                            schedules, and completion status.
                        </p>

                    </div>
                </article>
            </div>


            <!-- MATERNAL AND CHILD HEALTH -->
            <div class="col-md-6 col-lg-4">
                <article class="card service-card shadow-sm">
                    <div class="card-body">

                        <h3>Maternal and Child Health</h3>

                        <p>
                            Monitor pregnancy records, prenatal
                            visits, and child growth.
                        </p>

                    </div>
                </article>
            </div>


            <!-- MEDICINE INVENTORY -->
            <div class="col-md-6 col-lg-4">
                <article class="card service-card shadow-sm">
                    <div class="card-body">

                        <h3>Medicine Inventory</h3>

                        <p>
                            Monitor medicine stocks, distribution
                            records, and low-stock alerts.
                        </p>

                    </div>
                </article>
            </div>


            <!-- APPOINTMENTS -->
            <div class="col-md-6 col-lg-4">
                <article class="card service-card shadow-sm">
                    <div class="card-body">

                        <h3>Appointments</h3>

                        <p>
                            Request clinic visits and receive
                            approved appointment schedules.
                        </p>

                    </div>
                </article>
            </div>

        </div>
    </section>


    <!-- SYSTEM USERS -->
    <section class="users-section py-5">
        <div class="container">

            <div class="mb-4">
                <h2 class="section-title">
                    Who Uses the System?
                </h2>

                <p class="section-description mb-0">
                    The system is designed for healthcare
                    personnel and barangay residents.
                </p>
            </div>

            <div class="row g-3">

                <!-- HEALTHCARE PERSONNEL -->
                <div class="col-md-6">
                    <article class="card user-card shadow-sm">
                        <div class="card-body">

                            <h3>Healthcare Personnel</h3>

                            <p class="mb-2">
                                Authorized users include:
                            </p>

                            <ul class="mb-3">
                                <li>System Administrators</li>
                                <li>Barangay Health Workers</li>
                                <li>Health Center Nurses</li>
                                <li>Municipal Health Officers</li>
                            </ul>

                            <p class="mb-0">
                                Staff accounts are created by
                                the System Administrator.
                            </p>

                        </div>
                    </article>
                </div>


                <!-- RESIDENTS AND PATIENTS -->
                <div class="col-md-6">
                    <article class="card user-card shadow-sm">
                        <div class="card-body">

                            <h3>Residents and Patients</h3>

                            <p>
                                Residents can register their own
                                accounts to access available
                                barangay health services.
                            </p>

                            <p class="mb-0">
                                The system allows residents to
                                manage their health information,
                                view available services, and
                                request clinic appointments.
                            </p>

                        </div>
                    </article>
                </div>

            </div>

        </div>
    </section>

</main>


<!-- FOOTER -->
<footer class="container py-4 text-center">

    <p class="mb-1 fw-semibold">
        Barangay Tawang, La Trinidad, Benguet
    </p>

    <p class="mb-0">
        Health Services and Community Health Monitoring System
    </p>

</footer>

</body>
</html>
