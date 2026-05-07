<?php

class Customer extends User {
    private $name;
    private $deliveryAddress;
    private $accountStatus;

    public function __construct($db, $userId = null) {
        parent::__construct($db);
        $this->userId = $userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    // Create customer after user registration
    public function createCustomer($userId, $name, $deliveryAddress, $accountStatus = true) {
        $query = "INSERT INTO Customer 
                  (customer_id, name, delivery_address, account_status)
                  VALUES 
                  (:customer_id, :name, :delivery_address, :account_status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":customer_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":delivery_address", $deliveryAddress);
        $stmt->bindParam(":account_status", $accountStatus, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    // Get customer profile
    public function getProfile() {
        $query = "SELECT 
                    u.user_id,
                    u.email,
                    u.first_name,
                    u.last_name,
                    u.phone,
                    u.registration_date,
                    c.name,
                    c.delivery_address,
                    c.account_status
                  FROM Customer c
                  INNER JOIN Users u ON c.customer_id = u.user_id
                  WHERE c.customer_id = :customer_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Browse products
    public function browseProduct($search = null, $category = null) {
        $query = "SELECT *
                  FROM Product
                  WHERE stock_count > 0";

        $params = [];

        if (!empty($search)) {
            $query .= " AND name LIKE :search";
            $params[":search"] = "%" . $search . "%";
        }

        if (!empty($category)) {
            $query .= " AND category = :category";
            $params[":category"] = $category;
        }

        $query .= " ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add product to cart
    public function addToCart($productId, $quantity = 1) {
        if ($quantity <= 0) {
            return false;
        }

        $productQuery = "SELECT product_id, price, stock_count
                         FROM Product
                         WHERE product_id = :product_id
                         LIMIT 1";

        $productStmt = $this->conn->prepare($productQuery);
        $productStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $productStmt->execute();

        if ($productStmt->rowCount() == 0) {
            return false;
        }

        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if ($product['stock_count'] < $quantity) {
            return false;
        }

        $cartId = $this->getOrCreateCart();

        $checkQuery = "SELECT cart_item_id, quantity
                       FROM Cart_Item
                       WHERE cart_id = :cart_id
                       AND product_id = :product_id
                       LIMIT 1";

        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
        $checkStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $cartItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $cartItem['quantity'] + $quantity;

            if ($product['stock_count'] < $newQuantity) {
                return false;
            }

            $updateQuery = "UPDATE Cart_Item
                            SET quantity = :quantity
                            WHERE cart_item_id = :cart_item_id";

            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":quantity", $newQuantity, PDO::PARAM_INT);
            $updateStmt->bindParam(":cart_item_id", $cartItem['cart_item_id'], PDO::PARAM_INT);

            return $updateStmt->execute();
        }

        $insertQuery = "INSERT INTO Cart_Item
                        (cart_id, product_id, quantity, price)
                        VALUES
                        (:cart_id, :product_id, :quantity, :price)";

        $insertStmt = $this->conn->prepare($insertQuery);

        $insertStmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
        $insertStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $insertStmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $insertStmt->bindParam(":price", $product['price']);

        return $insertStmt->execute();
    }

    // Private helper: get or create cart
    private function getOrCreateCart() {
        $query = "SELECT cart_id
                  FROM Cart
                  WHERE user_id = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            return $cart['cart_id'];
        }

        $sessionId = session_id();

        $insertQuery = "INSERT INTO Cart
                        (user_id, session_id)
                        VALUES
                        (:user_id, :session_id)";

        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $insertStmt->bindParam(":session_id", $sessionId);
        $insertStmt->execute();

        return $this->conn->lastInsertId();
    }

    // Add product to wishlist
    public function addToWishlist($productId) {
        $query = "INSERT IGNORE INTO Wishlist
                  (user_id, product_id)
                  VALUES
                  (:user_id, :product_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Remove product from wishlist
    public function removeFromWishlist($productId) {
        $query = "DELETE FROM Wishlist
                  WHERE user_id = :user_id
                  AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get wishlist products
    public function getWishlist() {
        $query = "SELECT 
                    w.favorite_id,
                    p.product_id,
                    p.name,
                    p.description,
                    p.brand,
                    p.category,
                    p.price,
                    p.image,
                    p.average_rating,
                    p.review_count
                  FROM Wishlist w
                  INNER JOIN Product p ON w.product_id = p.product_id
                  WHERE w.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Submit review
    public function submitReview($productId, $comment, $rating) {
        if ($rating < 0 || $rating > 5) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO Review
                      (product_id, user_id, rating, comment)
                      VALUES
                      (:product_id, :user_id, :rating, :comment)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":comment", $comment);

            $stmt->execute();

            $updateProductQuery = "UPDATE Product
                                   SET 
                                     average_rating = (
                                        SELECT AVG(rating)
                                        FROM Review
                                        WHERE product_id = :product_id
                                     ),
                                     review_count = (
                                        SELECT COUNT(*)
                                        FROM Review
                                        WHERE product_id = :product_id
                                     )
                                   WHERE product_id = :product_id";

            $updateStmt = $this->conn->prepare($updateProductQuery);
            $updateStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
            $updateStmt->execute();

            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get order status
    public function orderStatus($orderId) {
        $query = "SELECT status
                  FROM Orders
                  WHERE order_id = :order_id
                  AND user_id = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);

        $stmt->execute();

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order ? $order['status'] : false;
    }

    // Get customer orders
    public function getOrders() {
        $query = "SELECT *
                  FROM Orders
                  WHERE user_id = :user_id
                  ORDER BY order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update delivery address
    public function updateDeliveryAddress($deliveryAddress) {
        $query = "UPDATE Customer
                  SET delivery_address = :delivery_address
                  WHERE customer_id = :customer_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":delivery_address", $deliveryAddress);
        $stmt->bindParam(":customer_id", $this->userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>