<?php

class Coupon {
    private $conn;
    private $table = "Coupon";

    private $couponId;
    private $code;
    private $discount;
    private $expiryDate;
    private $adminId;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new coupon
    public function create($code, $discount, $expiryDate, $adminId = null) {
        if ($discount < 0 || $discount > 100) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . "
                  (code, discount, expiry_date, admin_id)
                  VALUES
                  (:code, :discount, :expiry_date, :admin_id)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":code" => $code,
            ":discount" => $discount,
            ":expiry_date" => $expiryDate,
            ":admin_id" => $adminId
        ]);
    }

    // Get coupon by id
    public function getById($couponId) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE coupon_id = :coupon_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coupon_id", $couponId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get coupon by code
    public function getByCode($code) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE code = :code
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all coupons
    public function getAll() {
        $query = "SELECT *
                  FROM " . $this->table . "
                  ORDER BY coupon_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update coupon
    public function update($couponId, $code, $discount, $expiryDate) {
        if ($discount < 0 || $discount > 100) {
            return false;
        }

        $query = "UPDATE " . $this->table . "
                  SET code = :code,
                      discount = :discount,
                      expiry_date = :expiry_date
                  WHERE coupon_id = :coupon_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":code" => $code,
            ":discount" => $discount,
            ":expiry_date" => $expiryDate,
            ":coupon_id" => $couponId
        ]);
    }

    // Delete coupon
    public function delete($couponId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE coupon_id = :coupon_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coupon_id", $couponId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Check coupon is valid
    public function isValid($code) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE code = :code
                  AND expiry_date >= CURDATE()
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Apply coupon on total amount
    public function applyCoupon($code, $totalAmount) {
        $coupon = $this->isValid($code);

        if (!$coupon) {
            return false;
        }

        $discountPercent = $coupon['discount'];
        $discountAmount = ($totalAmount * $discountPercent) / 100;
        $finalTotal = $totalAmount - $discountAmount;

        return [
            "coupon_id" => $coupon['coupon_id'],
            "code" => $coupon['code'],
            "total_before_discount" => $totalAmount,
            "discount_percent" => $discountPercent,
            "discount_amount" => $discountAmount,
            "total_after_discount" => $finalTotal
        ];
    }
}

?>