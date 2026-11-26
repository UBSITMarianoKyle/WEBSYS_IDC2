<?php
    require_once __DIR__ . "/dbconn.php";

    class HealthSystem
    {
        private $pdo;

        function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

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
        
        public function show_resident_profile($user_id)
        {
            $sql = "SELECT * FROM residents WHERE user_id = :user_id";
            $prep = $this->pdo->prepare($sql);
            $prep->execute([":user_id" => $user_id]);

            return $prep->fetch();
        }
        public function show_updated_resident($resident_id)
        {
            $sql = "SELECT * FROM residents WHERE resident_id = :resident_id";
            $prep = $this->pdo->prepare($sql);
            $prep->execute([":resident_id" => $resident_id]);

            return $prep->fetch();
        }

        public function update_resident($resident_id, $fname, $lname, $birth_date, $sex, $address, $household_no, $contact, $health_condition, $allergies, $emergency_contact, $emergency_phone)
        {
            $sql = "UPDATE residents SET
                        first_name = :fname,
                        last_name = :lname,
                        birth_date = :birth_date,
                        sex = :sex,
                        address = :address,
                        household_no = :household_no,
                        contact = :contact,
                        health_condition = :health_condition,
                        allergies = :allergies,
                        emergency_contact = :emergency_contact,
                        emergency_phone = :emergency_phone
                    WHERE resident_id = :resident_id";

            $prep = $this->pdo->prepare($sql);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":fname" => $fname,
                ":lname" => $lname,
                ":birth_date" => $birth_date ?: null,
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


        /*public function show_updated_resident($resident_id)
        {
            $fetch = "SELECT * FROM residents WHERE resident_id = :resident_id";
            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":resident_id" => $resident_id]);

            return $prep->fetchAll();
        }*/

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

        public function show_resident_consultations($resident_id)
        {
            $fetch = "SELECT * FROM consultations
                    WHERE resident_id = :resident_id
                    ORDER BY consultation_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":resident_id" => $resident_id]);

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

        public function show_resident_vaccinations($resident_id)
        {
            $fetch = "SELECT * FROM vaccinations
                    WHERE resident_id = :resident_id
                    ORDER BY vaccination_date DESC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute([":resident_id" => $resident_id]);

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


        public function add_appointment($resident_id, $service_type, $appointment_date, $notes)
        {
            $sql = "INSERT INTO appointments
                    (resident_id, service_type, appointment_date, notes)
                    VALUES (:resident_id, :service_type, :appointment_date, :notes)";

            $prep = $this->pdo->prepare($sql);

            return $prep->execute([
                ":resident_id" => $resident_id,
                ":service_type" => $service_type,
                ":appointment_date" => $appointment_date,
                ":notes" => $notes
            ]);
        }

        public function show_resident_appointments($resident_id)
        {
            $sql = "SELECT * FROM appointments
                    WHERE resident_id = :resident_id
                    ORDER BY appointment_date DESC";

            $prep = $this->pdo->prepare($sql);
            $prep->execute([":resident_id" => $resident_id]);

            return $prep->fetchAll();
        }


        public function show_appointments()
        {
            $fetch = "SELECT a.*, r.first_name, r.last_name
                    FROM appointments a
                    INNER JOIN residents r ON a.resident_id = r.resident_id
                    ORDER BY a.status = 'pending' DESC, a.appointment_date ASC";

            $prep = $this->pdo->prepare($fetch);
            $prep->execute();

            return $prep->fetchAll();
        }

        public function change_medicine_stock($medicine_id, $resident_id, $staff_id, $transaction_type, $quantity, $remarks)
        {
            if($quantity <= 0 || !in_array($transaction_type, ["stock_in", "stock_out"], true)) return false;
            try {
                $this->pdo->beginTransaction();

                $check = "SELECT quantity, expiration_date FROM medicines WHERE medicine_id = :medicine_id FOR UPDATE";
                $prep = $this->pdo->prepare($check);
                $prep->execute([":medicine_id" => $medicine_id]);
                $medicine = $prep->fetch();

                if (!$medicine) {
                    $this->pdo->rollBack();
                    return false;
                }

                if ($transaction_type == "stock_out") {
                    if ($medicine['quantity'] < $quantity || (!empty($medicine['expiration_date']) && $medicine['expiration_date'] < date('Y-m-d'))) {
                        $this->pdo->rollBack();
                        return false;
                    }
                    $update = "UPDATE medicines SET quantity = quantity - :quantity WHERE medicine_id = :medicine_id";
                } else {
                    $update = "UPDATE medicines SET quantity = quantity + :quantity WHERE medicine_id = :medicine_id";
                }

                $prep = $this->pdo->prepare($update);
                $prep->execute([":quantity" => $quantity, ":medicine_id" => $medicine_id]);

                $this->add_medicine_transaction($medicine_id, $resident_id, $staff_id, $transaction_type, $quantity, $remarks);

                $this->pdo->commit();
                return true;
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                return false;
            }
        }

    public function add_supply($values, $staff = null){
        $this->pdo->beginTransaction();
        try {
        $prep=$this->pdo->prepare("INSERT INTO medicines (medicine_name,category,unit,quantity,minimum_stock,date_procured,procurement_type,source,acquisition_cost,expiration_date,batch_number,remarks) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $prep->execute($values);
        $id = $this->pdo->lastInsertId();
        if((int)$values[3] > 0 && $staff !== null){
            $this->add_medicine_transaction($id, null, $staff, 'stock_in', (int)$values[3], 'Initial received stock');
        }
        $this->pdo->commit();
        return true;
        } catch (Throwable $ex) {
            if($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $ex;
        }
    }
    public function distribute_supply($id,$qty,$staff,$remarks,$resident){
        if(!$this->change_medicine_stock($id,$resident,$staff,'stock_out',$qty,$remarks)) throw new PDOException('Stock distribution failed');
        return true;
    }
    
        public function report_pregnancies_count()
        {
            $fetch = "SELECT COUNT(*) FROM pregnancies";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_prenatal_visits_count()
        {
            $fetch = "SELECT COUNT(*) FROM prenatal_visits";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_child_health_count()
        {
            $fetch = "SELECT COUNT(*) FROM child_health";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_health_visits_count()
        {
            $fetch = "SELECT COUNT(*) FROM health_visits";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_residentcount()
        {
            $fetch = "SELECT COUNT(*) FROM residents";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_usercount()
        {
            $fetch = "SELECT COUNT(*) FROM users";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_medicinecount()
        {
            $fetch = "SELECT COUNT(*) FROM medicines";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_lowstockcount()
        {
            $fetch = "SELECT COUNT(*) FROM medicines
         WHERE quantity <= minimum_stock";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_vaccinated()
        {
            $fetch = "SELECT COUNT(DISTINCT resident_id)
         FROM vaccinations
         WHERE LOWER(TRIM(status)) = 'completed'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_appointments()
        {
            $fetch = "SELECT status, COUNT(*) AS total
         FROM appointments
         GROUP BY status";
            $result = $this->pdo->query($fetch);
            return $result->fetchAll();
        }

        public function report_medicines()
        {
            $fetch = "SELECT medicine_name, unit, quantity, minimum_stock
         FROM medicines
         WHERE category = 'Medicine'
         ORDER BY quantity ASC
         LIMIT 10";
            $result = $this->pdo->query($fetch);
            return $result->fetchAll();
        }

        public function report_consultations_period($year)
        {
            $fetch = "SELECT MONTH(consultation_date) AS month_no,
                COUNT(*) AS total
         FROM consultations
         WHERE YEAR(consultation_date) = ?
         GROUP BY MONTH(consultation_date)";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$year]);
            return $result->fetchAll();
        }

        public function report_medicine_transactions_period($year)
        {
            $fetch = "SELECT m.medicine_name, m.unit,
                SUM(t.quantity) AS total
         FROM medicine_transactions t
         JOIN medicines m ON m.medicine_id = t.medicine_id
         WHERE t.transaction_type = 'stock_out'
         AND m.category = 'Medicine'
         AND YEAR(t.transaction_date) = ?
         GROUP BY m.medicine_name, m.unit
         ORDER BY m.medicine_name";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$year]);
            return $result->fetchAll();
        }

        public function report_malecount()
        {
            $fetch = "SELECT COUNT(*) FROM residents
     WHERE LOWER(TRIM(sex)) = 'male'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_femalecount()
        {
            $fetch = "SELECT COUNT(*) FROM residents
     WHERE LOWER(TRIM(sex)) = 'female'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_householdcount()
        {
            $fetch = "SELECT COUNT(*) FROM households";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_vaccinatedcount()
        {
            $fetch = "SELECT COUNT(DISTINCT resident_id)
     FROM vaccinations
     WHERE LOWER(TRIM(status)) = 'completed'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_medicines_2()
        {
            $fetch = "SELECT medicine_name, unit, quantity, minimum_stock
     FROM medicines
     WHERE category = 'Medicine'
     ORDER BY medicine_name";
            $result = $this->pdo->query($fetch);
            return $result->fetchAll();
        }

        public function report_consultations_period_2($start, $end)
        {
            $fetch = "SELECT MONTH(consultation_date) AS month_no,
            COUNT(*) AS total
     FROM consultations
     WHERE consultation_date >= ?
     AND consultation_date < ?
     GROUP BY MONTH(consultation_date)";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchAll();
        }

        public function report_vaccinations_period($start, $end)
        {
            $fetch = "SELECT vaccine_name,
            COUNT(*) AS total,
            SUM(CASE WHEN LOWER(TRIM(status)) = 'completed'
                THEN 1 ELSE 0 END) AS completed
     FROM vaccinations
     WHERE vaccination_date >= ?
     AND vaccination_date < ?
     GROUP BY vaccine_name
     ORDER BY vaccine_name";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchAll();
        }

        public function report_medicine_transactions_period_2($start, $end)
        {
            $fetch = "SELECT m.medicine_name, m.unit,
            SUM(CASE WHEN t.transaction_type = 'stock_in'
                THEN t.quantity ELSE 0 END) AS stock_in,
            SUM(CASE WHEN t.transaction_type = 'stock_out'
                THEN t.quantity ELSE 0 END) AS stock_out
     FROM medicine_transactions t
     INNER JOIN medicines m
     ON t.medicine_id = m.medicine_id
     WHERE t.transaction_date >= ?
     AND t.transaction_date < ?
     AND m.category = 'Medicine'
     GROUP BY m.medicine_name, m.unit
     ORDER BY m.medicine_name";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchAll();
        }

        public function report_pregnancies_period($start, $end)
        {
            $fetch = "SELECT COUNT(*) FROM pregnancies
     WHERE registration_date >= ?
     AND registration_date < ?";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchColumn();
        }

        public function report_prenatal_visits_period($start, $end)
        {
            $fetch = "SELECT COUNT(*) FROM prenatal_visits
     WHERE visit_date >= ?
     AND visit_date < ?";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchColumn();
        }

        public function report_child_health_period($start, $end)
        {
            $fetch = "SELECT COUNT(*) FROM child_health
     WHERE visit_date >= ?
     AND visit_date < ?";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchColumn();
        }

        public function report_health_visits_period($start, $end)
        {
            $fetch = "SELECT COUNT(*) FROM health_visits
     WHERE scheduled_date >= ?
     AND scheduled_date < ?";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchColumn();
        }

        public function report_appointments_period($start, $end)
        {
            $fetch = "SELECT status, COUNT(*) AS total
     FROM appointments
     WHERE appointment_date >= ?
     AND appointment_date < ?
     GROUP BY status";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$start, $end]);
            return $result->fetchAll();
        }

        public function report_todayconsultations()
        {
            $fetch = "SELECT COUNT(*) FROM consultations
     WHERE consultation_date = CURDATE()";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_pending()
        {
            $fetch = "SELECT COUNT(*) FROM appointments
     WHERE status = 'pending'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_visits()
        {
            $fetch = "SELECT COUNT(*) FROM health_visits
     WHERE scheduled_date >= CURDATE()
     AND LOWER(status) = 'scheduled'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_vaccinations()
        {
            $fetch = "SELECT COUNT(*) FROM vaccinations
     WHERE next_schedule >= CURDATE()";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_lowstock()
        {
            $fetch = "SELECT COUNT(*) FROM medicines
     WHERE quantity <= minimum_stock
     AND category = 'Medicine'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_appointments_2()
        {
            $fetch = "SELECT status, COUNT(*) AS total
     FROM appointments
     GROUP BY status";
            $result = $this->pdo->query($fetch);
            return $result->fetchAll();
        }

        public function report_upcomingvaccines()
        {
            $fetch = "SELECT vaccine_name, next_schedule,
            COUNT(*) AS total
     FROM vaccinations
     WHERE next_schedule >= CURDATE()
     GROUP BY vaccine_name, next_schedule
     ORDER BY next_schedule ASC
     LIMIT 10";
            $result = $this->pdo->query($fetch);
            return $result->fetchAll();
        }

        public function report_upcomingvisits()
        {
            $fetch = "SELECT visit_type, scheduled_date,
            COUNT(*) AS total
     FROM health_visits
     WHERE scheduled_date >= CURDATE()
     AND LOWER(status) = 'scheduled'
     GROUP BY visit_type, scheduled_date
     ORDER BY scheduled_date ASC
     LIMIT 10";
            $result = $this->pdo->query($fetch);
            return $result->fetchAll();
        }

        public function report_consultations_period_3($date)
        {
            $fetch = "SELECT COUNT(*) FROM consultations
         WHERE consultation_date = ?";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$date]);
            return $result->fetchColumn();
        }

        public function report_totalvaccinations()
        {
            $fetch = "SELECT COUNT(*) FROM vaccinations";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_completedvaccinations()
        {
            $fetch = "SELECT COUNT(*) FROM vaccinations
     WHERE LOWER(TRIM(status)) = 'completed'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_upcomingprenatal()
        {
            $fetch = "SELECT COUNT(*) FROM prenatal_visits
     WHERE next_visit >= CURDATE()";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_upcomingchildvisits()
        {
            $fetch = "SELECT COUNT(*) FROM child_health
     WHERE next_visit >= CURDATE()";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_upcomingappointments()
        {
            $fetch = "SELECT COUNT(*) FROM appointments
     WHERE appointment_date >= NOW()
     AND status = 'approved'";
            $result = $this->pdo->query($fetch);
            return $result->fetchColumn();
        }

        public function report_consultations_period_4($year)
        {
            $fetch = "SELECT MONTH(consultation_date) AS month_no,
            COUNT(*) AS total
     FROM consultations
     WHERE YEAR(consultation_date) = ?
     GROUP BY MONTH(consultation_date)";
            $result = $this->pdo->prepare($fetch);
            $result->execute([$year]);
            return $result->fetchAll();
        }
}
?>
