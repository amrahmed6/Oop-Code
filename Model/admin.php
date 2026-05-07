<?php

class Admin extends User {
    private $adminId;

    public function __construct($db, $adminId = null) {
        parent::__construct($db);
        $this->adminId = $adminId;
    }

    public function setAdminId($adminId) {
        $this->adminId = $adminId;
    }

    public function createAdmin($userId) {
        $query = "INSERT INTO Admin (admin_id)
                  VALUES (:admin_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":admin_id", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function isAdmin($userId) {
        $query = "SELECT admin_id 
                  FROM Admin 
                  WHERE admin_id = :admin_id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":admin_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function addProduct($name, $description, $brand, $category, $price, $stockCount, $image = null) {
        $query = "INSERT INTO Product
                  (name, description, brand, category, price, stock_count, image, admin_id)
                  VALUES
                  (:name, :description, :brand, :category, :price, :stock_count, :image, :admin_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":brand", $brand);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":stock_count", $stockCount, PDO::PARAM_INT);
        $stmt->bindParam(":image", $image);
        $stmt->bindParam(":admin_id", $this->adminId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateProduct($productId, $data) {
        $allowedFields = [
            "name",
            "description",
            "brand",
            "category",
            "price",
            "stock_count",
            "image"
        ];

        $fields = [];
        $params = [
            ":product_id" => $productId
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE Product 
                  SET " . implode(", ", $fields) . "
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    public function deleteProduct($productId) {
        $query = "DELETE FROM Product
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function manageCategories($productId, $category) {
        $query = "UPDATE Product
                  SET category = :category
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function createCoupon($code, $discount, $expiryDate) {
        $query = "INSERT INTO Coupon
                  (code, discount, expiry_date, admin_id)
                  VALUES
                  (:code, :discount, :expiry_date, :admin_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":discount", $discount);
        $stmt->bindParam(":expiry_date", $expiryDate);
        $stmt->bindParam(":admin_id", $this->adminId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateCoupon($couponId, $code, $discount, $expiryDate) {
        $query = "UPDATE Coupon
                  SET code = :code,
                      discount = :discount,
                      expiry_date = :expiry_date
                  WHERE coupon_id = :coupon_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":discount", $discount);
        $stmt->bindParam(":expiry_date", $expiryDate);
        $stmt->bindParam(":coupon_id", $couponId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteCoupon($couponId) {
        $query = "DELETE FROM Coupon
                  WHERE coupon_id = :coupon_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coupon_id", $couponId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function generateReport() {
        $report = [];

        $queries = [
            "total_users" => "SELECT COUNT(*) AS total FROM Users",
            "total_customers" => "SELECT COUNT(*) AS total FROM Customer",
            "total_products" => "SELECT COUNT(*) AS total FROM Product",
            "total_orders" => "SELECT COUNT(*) AS total FROM Orders",
            "total_sales" => "SELECT COALESCE(SUM(total_amount), 0) AS total FROM Orders",
            "total_coupons" => "SELECT COUNT(*) AS total FROM Coupon"
        ];

        foreach ($queries as $key => $query) {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $report[$key] = $result['total'];
        }

        return $report;
    }
}

?>