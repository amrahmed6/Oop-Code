<?php

class Product {
    private $conn;
    private $table = "Product";

    private $productId;
    private $name;
    private $description;
    private $brand;
    private $category;
    private $price;
    private $stockCount;
    private $image;
    private $averageRating;
    private $reviewCount;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create product
    public function create($name, $description, $brand, $category, $price, $stockCount, $image = null, $adminId = null) {
        if ($price < 0 || $stockCount < 0) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . "
                  (name, description, brand, category, price, stock_count, image, admin_id)
                  VALUES
                  (:name, :description, :brand, :category, :price, :stock_count, :image, :admin_id)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":name" => $name,
            ":description" => $description,
            ":brand" => $brand,
            ":category" => $category,
            ":price" => $price,
            ":stock_count" => $stockCount,
            ":image" => $image,
            ":admin_id" => $adminId
        ]);
    }

    // Get product by id
    public function getById($productId) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE product_id = :product_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all products
    public function getAll() {
        $query = "SELECT *
                  FROM " . $this->table . "
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search products
    public function search($keyword) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE name LIKE :keyword_name
                     OR description LIKE :keyword_description
                     OR brand LIKE :keyword_brand
                     OR category LIKE :keyword_category
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);

        $searchKeyword = "%" . $keyword . "%";
        $stmt->bindValue(":keyword_name", $searchKeyword);
        $stmt->bindValue(":keyword_description", $searchKeyword);
        $stmt->bindValue(":keyword_brand", $searchKeyword);
        $stmt->bindValue(":keyword_category", $searchKeyword);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get products by category
    public function getByCategory($category) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE category = :category
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update product
    public function update($productId, $data) {
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

        if (isset($data['price']) && (float)$data['price'] < 0) {
            return false;
        }

        if (isset($data['stock_count']) && (int)$data['stock_count'] < 0) {
            return false;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE " . $this->table . "
                  SET " . implode(", ", $fields) . "
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    // Delete product
    public function delete($productId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get product details
    public function getDetails($productId) {
        $product = $this->getById($productId);

        if (!$product) {
            return false;
        }

        return [
            "product_id" => $product["product_id"],
            "name" => $product["name"],
            "description" => $product["description"],
            "brand" => $product["brand"],
            "category" => $product["category"],
            "price" => $product["price"],
            "stock_count" => $product["stock_count"],
            "image" => $product["image"],
            "average_rating" => $product["average_rating"],
            "review_count" => $product["review_count"]
        ];
    }

    // Check product stock
    public function isInStock($productId, $count = 1) {
        $query = "SELECT stock_count
                  FROM " . $this->table . "
                  WHERE product_id = :product_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return false;
        }

        return $product["stock_count"] >= $count;
    }

    // Decrease stock after order
    public function decreaseStock($productId, $quantity) {
        if ($quantity <= 0) {
            return false;
        }

        $query = "UPDATE " . $this->table . "
                  SET stock_count = stock_count - :quantity_remove
                  WHERE product_id = :product_id
                  AND stock_count >= :quantity_check";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":quantity_remove", (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(":quantity_check", (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(":product_id", (int)$productId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Increase stock if order cancelled
    public function increaseStock($productId, $quantity) {
        if ($quantity <= 0) {
            return false;
        }

        $query = "UPDATE " . $this->table . "
                  SET stock_count = stock_count + :quantity
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Calculate average rating from Review table
    public function calculateAverageRating($productId) {
        $query = "SELECT 
                    COALESCE(AVG(rating), 0) AS average_rating,
                    COUNT(*) AS review_count
                  FROM Review
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "average_rating" => round($result["average_rating"], 2),
            "review_count" => $result["review_count"]
        ];
    }

    // Update average_rating and review_count in Product table
    public function updateRating($productId) {
        $ratingData = $this->calculateAverageRating($productId);

        $query = "UPDATE " . $this->table . "
                  SET average_rating = :average_rating,
                      review_count = :review_count
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":average_rating" => $ratingData["average_rating"],
            ":review_count" => $ratingData["review_count"],
            ":product_id" => $productId
        ]);
    }

    // Get product reviews
    public function getReviews($productId) {
        $query = "SELECT 
                    r.review_id,
                    r.product_id,
                    r.user_id,
                    r.rating,
                    r.comment,
                    u.first_name,
                    u.last_name
                  FROM Review r
                  INNER JOIN Users u ON r.user_id = u.user_id
                  WHERE r.product_id = :product_id
                  ORDER BY r.review_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>