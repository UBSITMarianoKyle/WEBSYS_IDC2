<?php
declare(strict_types=1);

class HealthSystem
{
	public function __construct(private PDO $pdo)
	{
	}

	public function login(string $username, string $password): ?array
	{
		$query = $this->pdo->prepare(
			"SELECT users.*, residents.first_name AS resident_first_name, residents.last_name AS resident_last_name
			 FROM users
			 LEFT JOIN residents ON residents.id = users.resident_id
			 WHERE users.username = :username
			 LIMIT 1"
		);
		$query->execute(["username" => $username]);
		$user = $query->fetch();
		$storedPassword = is_array($user)
			? ($user["password_hash"] ?? $user["password"] ?? null)
			: null;

		if (!is_array($user) || !is_string($storedPassword) || !password_verify($password, $storedPassword)) {
			return null;
		}

		return $user;
	}

	public function households(): array
	{
		$query = $this->pdo->query(
			"SELECT id, CONCAT(household_no, ' - ', purok) AS label
			 FROM households
			 ORDER BY household_no"
		);

		return $query->fetchAll();
	}

	public function registerResident(array $data, string $username, string $password): void
	{
		$this->pdo->beginTransaction();

		try {
			$residentQuery = $this->pdo->prepare(
				"INSERT INTO residents
					(household_id, first_name, last_name, birthdate, sex, contact, emergency_name, emergency_relation, emergency_contact)
				 VALUES
					(:household_id, :first_name, :last_name, :birthdate, :sex, :contact, :emergency_name, :emergency_relation, :emergency_contact)"
			);
			$residentQuery->execute([
				"household_id" => (int) $data["household_id"],
				"first_name" => $data["first_name"],
				"last_name" => $data["last_name"],
				"birthdate" => $data["birthdate"],
				"sex" => $data["sex"],
				"contact" => $data["contact"] !== "" ? $data["contact"] : null,
				"emergency_name" => $data["emergency_name"],
				"emergency_relation" => $data["emergency_relation"],
				"emergency_contact" => $data["emergency_contact"],
			]);
			$residentId = $this->pdo->lastInsertId();

			$userQuery = $this->pdo->prepare(
				"INSERT INTO users (username, password, full_name, role, resident_id)
				 VALUES (:username, :password, :full_name, 'resident', :resident_id)"
			);
			$userQuery->execute([
				"username" => $username,
				"password" => password_hash($password, PASSWORD_DEFAULT),
				"full_name" => $data["first_name"] . " " . $data["last_name"],
				"resident_id" => $residentId,
			]);

			$this->pdo->commit();
		} catch (Throwable $exception) {
			if ($this->pdo->inTransaction()) {
				$this->pdo->rollBack();
			}

			throw $exception;
		}
	}
}