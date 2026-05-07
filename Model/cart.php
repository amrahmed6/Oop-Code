<?php

class Cart {
    private $conn;
    private $cartId;
    private $userId;
    private $sessionId;

    public function __construct($db, $userId = null, $sessionId = null) {
        $this->conn = $db;
        $this->userId = $userId;
        $this->sessionId = $sessionId;

        if ($userId !== null || $sessionId !== null) {
            $this->cartId = $this->getOrCreateCart();
        }
    }

    private function getOrCreateCart() {
        if ($this->userId !== null) {
            $query = "SELECT cart_id FROM Cart WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        } else {
            $query = "SELECT cart_id FROM Cart WHERE session_id = :session_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":session_id", $this->sessionId);
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            return $cart['cart_id'];
        }

        $insertQuery = "INSERT INTO Cart (user_id, session_id) 
                        VALUES (:user_id, :session_id)";

        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->bindParam(":user_id", $this->userId);
        $insertStmt->bindParam(":session_id", $this->sessionId);
        $insertStmt->execute();

        return $this->conn->lastInsertId();
    }

    public function getCartId() {
        return $this->cartId;
    }

    public function addItem($productId, $quantity = 1) {
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

        $checkQuery = "SELECT cart_item_id, quantity 
                       FROM Cart_Item 
                       WHERE cart_id = :cart_id 
                       AND product_id = :product_id 
                       LIMIT 1";

        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $checkStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $cartItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $cartItem['quantity'] + $quantity;

            if ($product['stock_count'] < $newQuantity) {
                return false;
            }

            return $this->updateQuantity($cartItem['cart_item_id'], $newQuantity);
        }

        $insertQuery = "INSERT INTO Cart_Item 
                        (cart_id, product_id, quantity, price)
                        VALUES 
                        (:cart_id, :product_id, :quantity, :price)";

        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $insertStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $insertStmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $insertStmt->bindParam(":price", $product['price']);

        return $insertStmt->execute();
    }

    public function removeItem($cartItemId) {
        $query = "DELETE FROM Cart_Item 
                  WHERE cart_item_id = :cart_item_id 
                  AND cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateQuantity($cartItemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        $checkQuery = "SELECT ci.product_id, p.stock_count
                       FROM Cart_Item ci
                       INNER JOIN Product p ON ci.product_id = p.product_id
                       WHERE ci.cart_item_id = :cart_item_id
                       AND ci.cart_id = :cart_id
                       LIMIT 1";

        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $checkStmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) {
            return false;
        }

        $item = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($item['stock_count'] < $quantity) {
            return false;
        }

        $query = "UPDATE Cart_Item
                  SET quantity = :quantity
                  WHERE cart_item_id = :cart_item_id
                  AND cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getItems() {
        $query = "SELECT 
                    ci.cart_item_id,
                    ci.cart_id,
                    ci.product_id,
                    p.name,
                    p.image,
                    ci.quantity,
                    ci.price,
                    ci.quantity * ci.price AS total
                  FROM Cart_Item ci
                  INNER JOIN Product p ON ci.product_id = p.product_id
                  WHERE ci.cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotal() {
        $query = "SELECT SUM(quantity * price) AS total
                  FROM Cart_Item
                  WHERE cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    public function applyCoupon($couponCode) {
        $query = "SELECT coupon_id, code, discount, expiry_date
                  FROM Coupon
                  WHERE code = :code
                  AND expiry_date >= CURDATE()
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $couponCode);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        $subtotal = $this->getTotal();
        $discountAmount = ($subtotal * $coupon['discount']) / 100;
        $finalTotal = $subtotal - $discountAmount;

        return [
            "coupon_id" => $coupon['coupon_id'],
            "code" => $coupon['code'],
            "subtotal" => $subtotal,
            "discount_percent" => $coupon['discount'],
            "discount_amount" => $discountAmount,
            "final_total" => $finalTotal
        ];
    }

    public function clear() {
        $query = "DELETE FROM Cart_Item
                  WHERE cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>