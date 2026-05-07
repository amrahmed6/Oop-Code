<?php

class Payment {
    private $conn;
    private $table = "Payment";

    private $paymentId;
    private $orderId;
    private $amount;
    private $paymentMethod;
    private $transactionId;
    private $status;
    private $paymentDate;

    public function __construct($db) {
        $this->conn = $db;
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

        return $stmt->execute();
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