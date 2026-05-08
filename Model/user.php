<?php

class User {
    protected $conn;
    private $table = "Users";

    protected $userId;
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $phone;
    private $registrationDate;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function registration($email, $password, $firstName, $lastName, $phone) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (strlen($password) < 6) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . "
                  (email, password, first_name, last_name, phone, registration_date)
                  VALUES
                  (:email, :password, :first_name, :last_name, :phone, CURDATE())";

        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            return $stmt->execute([
                ":email" => $email,
                ":password" => $hashedPassword,
                ":first_name" => trim($firstName),
                ":last_name" => trim($lastName),
                ":phone" => trim($phone)
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT 
                    u.*,
                    c.account_status
                  FROM " . $this->table . " u
                  LEFT JOIN Customer c ON c.customer_id = u.user_id
                  WHERE u.email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Blocked customers cannot login. Admins do not have a Customer row.
            if ($user['account_status'] !== null && (int)$user['account_status'] === 0) {
                return false;
            }

            if (password_verify($password, $user['password'])) {
                $this->userId = $user['user_id'];
                $this->email = $user['email'];
                $this->firstName = $user['first_name'];
                $this->lastName = $user['last_name'];
                $this->phone = $user['phone'];

                return $user;
            }
        }

        return false;
    }

    // Get user by id
    public function getUserById($userId) {
        $query = "SELECT user_id, email, first_name, last_name, phone, registration_date
                  FROM " . $this->table . "
                  WHERE user_id = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update profile
    public function updateProfile($userId, $firstName, $lastName, $phone) {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table . "
                      SET first_name = :first_name,
                          last_name = :last_name,
                          phone = :phone
                      WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":first_name" => trim($firstName),
                ":last_name" => trim($lastName),
                ":phone" => trim($phone),
                ":user_id" => $userId
            ]);

            // Keep Customer.name synced with Users.first_name + Users.last_name.
            $fullName = trim($firstName . " " . $lastName);
            $customerQuery = "UPDATE Customer
                              SET name = :name
                              WHERE customer_id = :user_id";

            $customerStmt = $this->conn->prepare($customerQuery);
            $customerStmt->execute([
                ":name" => $fullName,
                ":user_id" => $userId
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Change password
    public function changePassword($userId, $oldPassword, $newPassword) {
        if (strlen($newPassword) < 6) {
            return false;
        }

        $query = "SELECT password FROM " . $this->table . "
                  WHERE user_id = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($oldPassword, $user['password'])) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateQuery = "UPDATE " . $this->table . "
                                SET password = :password
                                WHERE user_id = :user_id";

                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(":password", $newHashedPassword);
                $updateStmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

                return $updateStmt->execute();
            }
        }

        return false;
    }

    // Logout
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        return true;
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT user_id FROM " . $this->table . "
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
