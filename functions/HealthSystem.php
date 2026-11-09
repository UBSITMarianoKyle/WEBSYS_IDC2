<?php
    require_once "dbconn.php";

    class HealthSystem
    {
        function __construct(private $pdo) {}

        public function register($username, $password, $role)
        {
            $check = "SELECT * FROM users WHERE username = :username";
            $prep = $this->pdo->prepare($check);
            $prep->execute([":username" => $username]);

            if ($prep->fetch()) {
                return false;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert = "INSERT INTO users (username, password, role)
                    VALUES (:username, :password, :role)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":username" => $username,
                ":password" => $hashed_password,
                ":role" => $role
            ]);
        }

        public function login($username)
        {
            $fetch = "SELECT * FROM users
                    WHERE username = :username AND status = 'active'";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":username" => $username]);

            return $prep->fetch();
        }

        public function add_resident($user_id, $first_name, $last_name, $birth_date, $sex, $address, $household_no, $contact, $health_condition, $allergies, $emergency_contact, $emergency_phone)
        {
            $insert = "INSERT INTO residents
                    (user_id, first_name, last_name, birth_date, sex, address, household_no, contact, health_condition, allergies, emergency_contact, emergency_phone)
                    VALUES (:user_id, :first_name, :last_name, :birth_date, :sex, :address, :household_no, :contact, :health_condition, :allergies, :emergency_contact, :emergency_phone)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":user_id" => $user_id,
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":birth_date" => $birth_date,
                ":sex" => $sex,
                ":address" => $address,
                ":household_no" => $household_no,
                ":contact" => $contact,
                ":health_condition" => $health_condition,
                ":allergies" => $allergies,
                ":emergency_contact" => $emergency_contact,
                ":emergency_phone" => $emergency_phone
            ]);
        }

        public function show_residents()
        {
            $fetch = "SELECT * FROM residents";
            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function delete_resident($resident_id)
        {
            $delete = "DELETE FROM residents WHERE resident_id = :resident_id";
            $prep = $this->pdo->prepare($delete);
            $prep->execute([":resident_id" => $resident_id]);

            return $prep->rowCount();
        }

        public function update_resident($resident_id, $first_name, $last_name, $birth_date, $sex, $address, $contact)
        {
            $update = "UPDATE residents
                    SET first_name = :first_name,
                        last_name = :last_name,
                        birth_date = :birth_date,
                        sex = :sex,
                        address = :address,
                        contact = :contact
                    WHERE resident_id = :resident_id";

            $prep = $this->pdo->prepare($update);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":birth_date" => $birth_date,
                ":sex" => $sex,
                ":address" => $address,
                ":contact" => $contact
            ]);
        }

        public function show_updated_resident($resident_id)
        {
            $fetch = "SELECT * FROM residents WHERE resident_id = :resident_id";
            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":resident_id" => $resident_id]);

            return $prep->fetchAll();
        }

        public function add_consultation($resident_id, $staff_id, $consultation_date, $symptoms, $assessment, $treatment)
        {
            $insert = "INSERT INTO consultations
                    (resident_id, staff_id, consultation_date, symptoms, assessment, treatment)
                    VALUES (:resident_id, :staff_id, :consultation_date, :symptoms, :assessment, :treatment)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":staff_id" => $staff_id,
                ":consultation_date" => $consultation_date,
                ":symptoms" => $symptoms,
                ":assessment" => $assessment,
                ":treatment" => $treatment
            ]);
        }

        public function show_consultations()
        {
            $fetch = "SELECT c.*, r.first_name, r.last_name
                    FROM consultations c
                    INNER JOIN residents r ON c.resident_id = r.resident_id
                    ORDER BY c.consultation_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_vaccination($resident_id, $vaccine_name, $dose_number, $vaccination_date, $next_schedule, $status, $recorded_by)
        {
            $insert = "INSERT INTO vaccinations
                    (resident_id, vaccine_name, dose_number, vaccination_date, next_schedule, status, recorded_by)
                    VALUES (:resident_id, :vaccine_name, :dose_number, :vaccination_date, :next_schedule, :status, :recorded_by)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":vaccine_name" => $vaccine_name,
                ":dose_number" => $dose_number,
                ":vaccination_date" => $vaccination_date,
                ":next_schedule" => $next_schedule,
                ":status" => $status,
                ":recorded_by" => $recorded_by
            ]);
        }

        public function show_vaccinations()
        {
            $fetch = "SELECT v.*, r.first_name, r.last_name
                    FROM vaccinations v
                    INNER JOIN residents r ON v.resident_id = r.resident_id
                    ORDER BY v.vaccination_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_pregnancy($resident_id, $registration_date, $expected_delivery, $status)
        {
            $insert = "INSERT INTO pregnancies
                    (resident_id, registration_date, expected_delivery, status)
                    VALUES (:resident_id, :registration_date, :expected_delivery, :status)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":registration_date" => $registration_date,
                ":expected_delivery" => $expected_delivery,
                ":status" => $status
            ]);
        }

        public function show_pregnancies()
        {
            $fetch = "SELECT p.*, r.first_name, r.last_name
                    FROM pregnancies p
                    INNER JOIN residents r ON p.resident_id = r.resident_id
                    ORDER BY p.registration_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_prenatal_visit($pregnancy_id, $visit_date, $blood_pressure, $weight, $notes, $next_visit, $staff_id)
        {
            $insert = "INSERT INTO prenatal_visits
                    (pregnancy_id, visit_date, blood_pressure, weight, notes, next_visit, staff_id)
                    VALUES (:pregnancy_id, :visit_date, :blood_pressure, :weight, :notes, :next_visit, :staff_id)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":pregnancy_id" => $pregnancy_id,
                ":visit_date" => $visit_date,
                ":blood_pressure" => $blood_pressure,
                ":weight" => $weight,
                ":notes" => $notes,
                ":next_visit" => $next_visit,
                ":staff_id" => $staff_id
            ]);
        }

        public function show_prenatal_visits()
        {
            $fetch = "SELECT pv.*, r.first_name, r.last_name
                    FROM prenatal_visits pv
                    INNER JOIN pregnancies p ON pv.pregnancy_id = p.pregnancy_id
                    INNER JOIN residents r ON p.resident_id = r.resident_id
                    ORDER BY pv.visit_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_child_health($resident_id, $visit_date, $height, $weight, $notes, $next_visit)
        {
            $insert = "INSERT INTO child_health
                    (resident_id, visit_date, height, weight, notes, next_visit)
                    VALUES (:resident_id, :visit_date, :height, :weight, :notes, :next_visit)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":visit_date" => $visit_date,
                ":height" => $height,
                ":weight" => $weight,
                ":notes" => $notes,
                ":next_visit" => $next_visit
            ]);
        }

        public function show_child_health()
        {
            $fetch = "SELECT ch.*, r.first_name, r.last_name
                    FROM child_health ch
                    INNER JOIN residents r ON ch.resident_id = r.resident_id
                    ORDER BY ch.visit_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_health_visit($resident_id, $visit_type, $scheduled_date, $status, $notes)
        {
            $insert = "INSERT INTO health_visits
                    (resident_id, visit_type, scheduled_date, status, notes)
                    VALUES (:resident_id, :visit_type, :scheduled_date, :status, :notes)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":visit_type" => $visit_type,
                ":scheduled_date" => $scheduled_date,
                ":status" => $status,
                ":notes" => $notes
            ]);
        }

        public function show_health_visits()
        {
            $fetch = "SELECT hv.*, r.first_name, r.last_name
                    FROM health_visits hv
                    INNER JOIN residents r ON hv.resident_id = r.resident_id
                    ORDER BY hv.scheduled_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_medicine($medicine_name, $unit, $quantity, $minimum_stock, $expiration_date)
        {
            $insert = "INSERT INTO medicines
                    (medicine_name, unit, quantity, minimum_stock, expiration_date)
                    VALUES (:medicine_name, :unit, :quantity, :minimum_stock, :expiration_date)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":medicine_name" => $medicine_name,
                ":unit" => $unit,
                ":quantity" => $quantity,
                ":minimum_stock" => $minimum_stock,
                ":expiration_date" => $expiration_date
            ]);
        }

        public function show_medicines()
        {
            $fetch = "SELECT * FROM medicines ORDER BY medicine_name ASC";
            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function delete_medicine($medicine_id)
        {
            $delete = "DELETE FROM medicines WHERE medicine_id = :medicine_id";
            $prep = $this->pdo->prepare($delete);
            $prep->execute([":medicine_id" => $medicine_id]);

            return $prep->rowCount();
        }

        public function update_medicine($medicine_id, $medicine_name, $unit, $quantity, $minimum_stock, $expiration_date)
        {
            $update = "UPDATE medicines
                    SET medicine_name = :medicine_name,
                        unit = :unit,
                        quantity = :quantity,
                        minimum_stock = :minimum_stock,
                        expiration_date = :expiration_date
                    WHERE medicine_id = :medicine_id";

            $prep = $this->pdo->prepare($update);

            return $prep->execute([
                ":medicine_id" => $medicine_id,
                ":medicine_name" => $medicine_name,
                ":unit" => $unit,
                ":quantity" => $quantity,
                ":minimum_stock" => $minimum_stock,
                ":expiration_date" => $expiration_date
            ]);
        }

        public function show_updated_medicine($medicine_id)
        {
            $fetch = "SELECT * FROM medicines WHERE medicine_id = :medicine_id";
            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":medicine_id" => $medicine_id]);

            return $prep->fetchAll();
        }

        public function show_low_stock()
        {
            $fetch = "SELECT * FROM medicines
                    WHERE quantity <= minimum_stock
                    ORDER BY quantity ASC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_medicine_transaction($medicine_id, $resident_id, $staff_id, $transaction_type, $quantity, $remarks)
        {
            $insert = "INSERT INTO medicine_transactions
                    (medicine_id, resident_id, staff_id, transaction_type, quantity, remarks)
                    VALUES (:medicine_id, :resident_id, :staff_id, :transaction_type, :quantity, :remarks)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":medicine_id" => $medicine_id,
                ":resident_id" => $resident_id,
                ":staff_id" => $staff_id,
                ":transaction_type" => $transaction_type,
                ":quantity" => $quantity,
                ":remarks" => $remarks
            ]);
        }

        public function show_medicine_transactions()
        {
            $fetch = "SELECT mt.*, m.medicine_name, r.first_name, r.last_name
                    FROM medicine_transactions mt
                    INNER JOIN medicines m ON mt.medicine_id = m.medicine_id
                    LEFT JOIN residents r ON mt.resident_id = r.resident_id
                    ORDER BY mt.transaction_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function add_appointment($resident_id, $service_type, $appointment_date, $notes)
        {
            $insert = "INSERT INTO appointments (resident_id, service_type, appointment_date, notes)
                    VALUES (:resident_id, :service_type, :appointment_date, :notes)";

            $prep = $this->pdo->prepare($insert);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":service_type" => $service_type,
                ":appointment_date" => $appointment_date,
                ":notes" => $notes
            ]);
        }

        public function show_appointments()
        {
            $fetch = "SELECT a.*, r.first_name, r.last_name
                    FROM appointments a
                    INNER JOIN residents r ON a.resident_id = r.resident_id
                    ORDER BY a.appointment_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function delete_appointment($appointment_id)
        {
            $delete = "DELETE FROM appointments WHERE appointment_id = :appointment_id";
            $prep = $this->pdo->prepare($delete);
            $prep->execute([":appointment_id" => $appointment_id]);

            return $prep->rowCount();
        }

        public function update_appointment($appointment_id, $status, $approved_by)
        {
            $update = "UPDATE appointments SET status = :status, approved_by = :approved_by
                    WHERE appointment_id = :appointment_id";

            $prep = $this->pdo->prepare($update);

            return $prep->execute([
                ":appointment_id" => $appointment_id,
                ":status" => $status,
                ":approved_by" => $approved_by
            ]);
        }

        public function show_updated_appointment($appointment_id)
        {
            $fetch = "SELECT * FROM appointments WHERE appointment_id = :appointment_id";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":appointment_id" => $appointment_id]);

            return $prep->fetchAll();
        }

        public function dashboard_stats()
        {
            $fetch = "SELECT
                        (SELECT COUNT(*) FROM residents) AS total_residents,
                        (SELECT COUNT(*) FROM consultations) AS total_consultations,
                        (SELECT COUNT(*) FROM vaccinations) AS total_vaccinations,
                        (SELECT COUNT(*) FROM pregnancies WHERE status = 'ongoing') AS ongoing_pregnancies,
                        (SELECT COUNT(*) FROM child_health) AS child_health_records,
                        (SELECT COUNT(*) FROM appointments WHERE status = 'pending') AS pending_appointments,
                        (SELECT COUNT(*) FROM medicines WHERE quantity <= minimum_stock) AS low_stock";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetch();
        }
    }
?>
