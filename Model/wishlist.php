<?php

class Wishlist {
    private $conn;
    private $table = "Wishlist";

    private $favoriteId;
    private $userId;
    private $productId;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add product to wishlist
    public function add($userId, $productId) {
        // Check product exists
        $productQuery = "SELECT product_id
                         FROM Product
                         WHERE product_id = :product_id
                         LIMIT 1";

        $productStmt = $this->conn->prepare($productQuery);
        $productStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $productStmt->execute();

        if ($productStmt->rowCount() == 0) {
            return false;
        }

        $query = "INSERT IGNORE INTO " . $this->table . "
                  (user_id, product_id)
                  VALUES
                  (:user_id, :product_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Remove product from wishlist
    public function remove($userId, $productId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE user_id = :user_id
                  AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get all wishlist products for user
    public function getByUserId($userId) {
        $query = "SELECT 
                    w.favorite_id,
                    w.user_id,
                    w.product_id,
                    p.name,
                    p.description,
                    p.brand,
                    p.category,
                    p.price,
                    p.stock_count,
                    p.image,
                    p.average_rating,
                    p.review_count
                  FROM " . $this->table . " w
                  INNER JOIN Product p ON w.product_id = p.product_id
                  WHERE w.user_id = :user_id
                  ORDER BY w.favorite_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get wishlist item by id
    public function getById($favoriteId) {
        $query = "SELECT 
                    w.favorite_id,
                    w.user_id,
                    w.product_id,
                    p.name,
                    p.price,
                    p.image
                  FROM " . $this->table . " w
                  INNER JOIN Product p ON w.product_id = p.product_id
                  WHERE w.favorite_id = :favorite_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":favorite_id", $favoriteId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if product already exists in wishlist
    public function exists($userId, $productId) {
        $query = "SELECT favorite_id
                  FROM " . $this->table . "
                  WHERE user_id = :user_id
                  AND product_id = :product_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Clear all wishlist for user
    public function clear($userId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Count wishlist items
    public function countItems($userId) {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . "
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['total'] : 0;
    }
}

?>