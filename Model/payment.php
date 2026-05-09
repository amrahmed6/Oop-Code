<?php

class Payment {
    private $conn;
    private $table = "Payment";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureInstapayTable();
        $this->ensureVisaTable();
    }

    private function ensureInstapayTable() {
        $query = "CREATE TABLE IF NOT EXISTS Instapay_Transfer (
                    instapay_transfer_id INT AUTO_INCREMENT PRIMARY KEY,
                    payment_id INT NOT NULL UNIQUE,
                    order_id INT NOT NULL,
                    sender_phone VARCHAR(30) NOT NULL,
                    proof_image VARCHAR(255) NOT NULL,
                    admin_status VARCHAR(50) DEFAULT 'Pending',
                    admin_note TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    reviewed_at DATETIME NULL,
                    reviewed_by INT NULL,
                    FOREIGN KEY (payment_id) REFERENCES Payment(payment_id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                    FOREIGN KEY (reviewed_by) REFERENCES Admin(admin_id)
                        ON DELETE SET NULL
                        ON UPDATE CASCADE,
                    INDEX idx_instapay_order (order_id),
                    INDEX idx_instapay_status (admin_status)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->conn->exec($query);
    }

    private function ensureVisaTable() {
        $query = "CREATE TABLE IF NOT EXISTS Visa_Payment_Details (
                    visa_payment_id INT AUTO_INCREMENT PRIMARY KEY,
                    payment_id INT NOT NULL UNIQUE,
                    order_id INT NOT NULL,
                    cardholder_name VARCHAR(120) NOT NULL,
                    card_last4 VARCHAR(4) NOT NULL,
                    expiry_month TINYINT NOT NULL,
                    expiry_year SMALLINT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (payment_id) REFERENCES Payment(payment_id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE,
                    INDEX idx_visa_order (order_id)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->conn->exec($query);
    }

    // Create payment for order
    public function create($orderId, $paymentMethod, $transactionId = null, $status = "Completed") {
        $allowedStatus = ["Pending", "Completed", "Failed", "Refunded"];

        if (!in_array($status, $allowedStatus)) {
            return false;
        }

        // Get order total amount
        $orderQuery = "SELECT total_amount 
                       FROM Orders 
                       WHERE order_id = :order_id 
                       LIMIT 1";

        $orderStmt = $this->conn->prepare($orderQuery);
        $orderStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $orderStmt->execute();

        if ($orderStmt->rowCount() == 0) {
            return false;
        }

        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
        $amount = $order['total_amount'];

        // Check if order already has payment
        if ($this->getByOrderId($orderId)) {
            return false;
        }

        if ($transactionId === null) {
            $transactionId = "TXN" . time() . rand(1000, 9999);
        }

        $query = "INSERT INTO " . $this->table . "
                  (order_id, amount, payment_method, transaction_id, status, payment_date)
                  VALUES
                  (:order_id, :amount, :payment_method, :transaction_id, :status, CURDATE())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":payment_method", $paymentMethod);
        $stmt->bindParam(":transaction_id", $transactionId);
        $stmt->bindParam(":status", $status);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Create Instapay transfer proof row
    public function createInstapayTransfer($paymentId, $orderId, $senderPhone, $proofImage) {
        $query = "INSERT INTO Instapay_Transfer
                  (payment_id, order_id, sender_phone, proof_image, admin_status, created_at)
                  VALUES
                  (:payment_id, :order_id, :sender_phone, :proof_image, 'Pending', NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":sender_phone", $senderPhone);
        $stmt->bindParam(":proof_image", $proofImage);

        return $stmt->execute();
    }

    // Get Instapay transfer data by order id
    public function getInstapayByOrderId($orderId) {
        $query = "SELECT *
                  FROM Instapay_Transfer
                  WHERE order_id = :order_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Save safe Visa payment details only. Full card number and CVV are not stored.
    public function createVisaDetails($paymentId, $orderId, $cardholderName, $cardLast4, $expiryMonth, $expiryYear) {
        $query = "INSERT INTO Visa_Payment_Details
                  (payment_id, order_id, cardholder_name, card_last4, expiry_month, expiry_year, created_at)
                  VALUES
                  (:payment_id, :order_id, :cardholder_name, :card_last4, :expiry_month, :expiry_year, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":cardholder_name", $cardholderName);
        $stmt->bindParam(":card_last4", $cardLast4);
        $stmt->bindParam(":expiry_month", $expiryMonth, PDO::PARAM_INT);
        $stmt->bindParam(":expiry_year", $expiryYear, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get Visa details by order id
    public function getVisaByOrderId($orderId) {
        $query = "SELECT *
                  FROM Visa_Payment_Details
                  WHERE order_id = :order_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get payment by payment id
    public function getById($paymentId) {
        $query = "SELECT 
                    p.payment_id,
                    p.order_id,
                    p.amount,
                    p.payment_method,
                    p.transaction_id,
                    p.status,
                    p.payment_date,
                    o.user_id,
                    o.total_amount,
                    o.order_date
                  FROM " . $this->table . " p
                  INNER JOIN Orders o ON p.order_id = o.order_id
                  WHERE p.payment_id = :payment_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get payment by order id
    public function getByOrderId($orderId) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE order_id = :order_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update payment status
    public function updateStatus($paymentId, $status) {
        $allowedStatus = ["Pending", "Completed", "Failed", "Refunded"];

        if (!in_array($status, $allowedStatus)) {
            return false;
        }

        $query = "UPDATE " . $this->table . "
                  SET status = :status
                  WHERE payment_id = :payment_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Admin approves Instapay after confirming money was received
    public function approveInstapay($paymentId, $adminId) {
        try {
            $this->conn->beginTransaction();

            $payment = $this->getById($paymentId);
            if (!$payment || $payment['payment_method'] !== 'Instapay') {
                $this->conn->rollBack();
                return false;
            }

            $updatePayment = "UPDATE Payment
                              SET status = 'Completed'
                              WHERE payment_id = :payment_id";
            $stmt = $this->conn->prepare($updatePayment);
            $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
            $stmt->execute();

            $updateProof = "UPDATE Instapay_Transfer
                            SET admin_status = 'Approved', reviewed_at = NOW(), reviewed_by = :admin_id
                            WHERE payment_id = :payment_id";
            $stmt = $this->conn->prepare($updateProof);
            $stmt->bindParam(":admin_id", $adminId, PDO::PARAM_INT);
            $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
            $stmt->execute();

            $updateOrder = "UPDATE Orders
                            SET status = 'Processing'
                            WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($updateOrder);
            $stmt->bindParam(":order_id", $payment['order_id'], PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Admin rejects Instapay if transfer is not valid
    public function rejectInstapay($paymentId, $adminId, $note = '') {
        try {
            $this->conn->beginTransaction();

            $payment = $this->getById($paymentId);
            if (!$payment || $payment['payment_method'] !== 'Instapay') {
                $this->conn->rollBack();
                return false;
            }

            $updatePayment = "UPDATE Payment
                              SET status = 'Failed'
                              WHERE payment_id = :payment_id";
            $stmt = $this->conn->prepare($updatePayment);
            $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
            $stmt->execute();

            $updateProof = "UPDATE Instapay_Transfer
                            SET admin_status = 'Rejected', admin_note = :admin_note,
                                reviewed_at = NOW(), reviewed_by = :admin_id
                            WHERE payment_id = :payment_id";
            $stmt = $this->conn->prepare($updateProof);
            $stmt->bindParam(":admin_note", $note);
            $stmt->bindParam(":admin_id", $adminId, PDO::PARAM_INT);
            $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);
            $stmt->execute();

            $updateOrder = "UPDATE Orders
                            SET status = 'Payment Rejected'
                            WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($updateOrder);
            $stmt->bindParam(":order_id", $payment['order_id'], PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Update payment method
    public function updatePaymentMethod($paymentId, $paymentMethod) {
        $query = "UPDATE " . $this->table . "
                  SET payment_method = :payment_method
                  WHERE payment_id = :payment_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":payment_method", $paymentMethod);
        $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Refund payment
    public function refund($paymentId) {
        return $this->updateStatus($paymentId, "Refunded");
    }

    // Delete payment
    public function delete($paymentId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE payment_id = :payment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get all payments - for admin
    public function getAll() {
        $query = "SELECT 
                    p.payment_id,
                    p.order_id,
                    p.amount,
                    p.payment_method,
                    p.transaction_id,
                    p.status,
                    p.payment_date,
                    o.user_id,
                    o.order_date
                  FROM " . $this->table . " p
                  INNER JOIN Orders o ON p.order_id = o.order_id
                  ORDER BY p.payment_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get payments for specific customer
    public function getUserPayments($userId) {
        $query = "SELECT 
                    p.payment_id,
                    p.order_id,
                    p.amount,
                    p.payment_method,
                    p.transaction_id,
                    p.status,
                    p.payment_date
                  FROM " . $this->table . " p
                  INNER JOIN Orders o ON p.order_id = o.order_id
                  WHERE o.user_id = :user_id
                  ORDER BY p.payment_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
