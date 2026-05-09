<?php

class Order {
    private $conn;
    private $table = "Orders";
    private $lastError = "";

    private $orderId;
    private $userId;
    private $totalAmount;
    private $status;
    private $discount;
    private $shippingCost;
    private $trackingNumber;

    public function __construct($db, $userId = null) {
        $this->conn = $db;
        $this->userId = $userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function setLastError($message) {
        $this->lastError = $message;
    }

    private function ensureCustomerRecord() {
        $checkQuery = "SELECT customer_id FROM Customer WHERE customer_id = :user_id LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return true;
        }

        $userQuery = "SELECT user_id, first_name, last_name FROM Users WHERE user_id = :user_id LIMIT 1";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->setLastError("User account was not found.");
            return false;
        }

        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($name === '') {
            $name = 'Customer';
        }

        $insertQuery = "INSERT INTO Customer (customer_id, name, delivery_address, account_status)
                        VALUES (:customer_id, :name, '', TRUE)";
        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->bindParam(":customer_id", $this->userId, PDO::PARAM_INT);
        $insertStmt->bindParam(":name", $name);
        return $insertStmt->execute();
    }

    private function findCartId($sessionId = null) {
        // First try a logged-in cart that actually has items.
        $cartQuery = "SELECT c.cart_id
                      FROM Cart c
                      INNER JOIN Cart_Item ci ON ci.cart_id = c.cart_id
                      WHERE c.user_id = :user_id
                      GROUP BY c.cart_id
                      ORDER BY c.cart_id DESC
                      LIMIT 1";
        $cartStmt = $this->conn->prepare($cartQuery);
        $cartStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $cartStmt->execute();
        $cart = $cartStmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return $cart['cart_id'];
        }

        // If the product was added before login, attach the session cart to this user.
        if (!empty($sessionId)) {
            $guestCartQuery = "SELECT c.cart_id
                               FROM Cart c
                               INNER JOIN Cart_Item ci ON ci.cart_id = c.cart_id
                               WHERE c.session_id = :session_id
                               GROUP BY c.cart_id
                               ORDER BY c.cart_id DESC
                               LIMIT 1";
            $guestCartStmt = $this->conn->prepare($guestCartQuery);
            $guestCartStmt->bindParam(":session_id", $sessionId);
            $guestCartStmt->execute();
            $guestCart = $guestCartStmt->fetch(PDO::FETCH_ASSOC);

            if ($guestCart) {
                $updateCartQuery = "UPDATE Cart
                                    SET user_id = :user_id
                                    WHERE cart_id = :cart_id";
                $updateCartStmt = $this->conn->prepare($updateCartQuery);
                $updateCartStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
                $updateCartStmt->bindParam(":cart_id", $guestCart['cart_id'], PDO::PARAM_INT);
                $updateCartStmt->execute();

                return $guestCart['cart_id'];
            }
        }

        // Finally, return any account cart. createOrder() will show "cart is empty" if it has no items.
        $emptyCartQuery = "SELECT cart_id
                           FROM Cart
                           WHERE user_id = :user_id
                           ORDER BY cart_id DESC
                           LIMIT 1";
        $emptyCartStmt = $this->conn->prepare($emptyCartQuery);
        $emptyCartStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $emptyCartStmt->execute();
        $emptyCart = $emptyCartStmt->fetch(PDO::FETCH_ASSOC);

        return $emptyCart ? $emptyCart['cart_id'] : false;
    }

    // Create order from cart
    public function createOrder($shippingCost = 0, $couponCode = null, $sessionId = null) {
        $this->lastError = "";
        $shippingCost = (float)$shippingCost;

        if (empty($this->userId)) {
            $this->setLastError("You must login before placing an order.");
            return false;
        }

        try {
            $this->conn->beginTransaction();

            if (!$this->ensureCustomerRecord()) {
                $this->conn->rollBack();
                if ($this->lastError === "") {
                    $this->setLastError("Customer profile could not be created.");
                }
                return false;
            }

            $cartId = $this->findCartId($sessionId);

            if (!$cartId) {
                $this->conn->rollBack();
                $this->setLastError("No cart found for this account.");
                return false;
            }

            // Get cart items
            $itemsQuery = "SELECT 
                            ci.product_id,
                            ci.quantity,
                            ci.price,
                            p.name,
                            p.stock_count
                           FROM Cart_Item ci
                           INNER JOIN Product p ON ci.product_id = p.product_id
                           WHERE ci.cart_id = :cart_id";

            $itemsStmt = $this->conn->prepare($itemsQuery);
            $itemsStmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
            $itemsStmt->execute();

            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                $this->conn->rollBack();
                $this->setLastError("Your cart is empty.");
                return false;
            }

            // Calculate subtotal
            $subtotal = 0;

            foreach ($items as $item) {
                if ((int)$item['stock_count'] < (int)$item['quantity']) {
                    $this->conn->rollBack();
                    $this->setLastError("Not enough stock for " . $item['name'] . ".");
                    return false;
                }

                $subtotal += ((int)$item['quantity'] * (float)$item['price']);
            }

            // Apply coupon if exists
            $couponId = null;
            $discountAmount = 0;

            if (!empty($couponCode)) {
                $couponQuery = "SELECT coupon_id, discount
                                FROM Coupon
                                WHERE code = :code
                                AND expiry_date >= CURDATE()
                                LIMIT 1";

                $couponStmt = $this->conn->prepare($couponQuery);
                $couponStmt->bindParam(":code", $couponCode);
                $couponStmt->execute();

                if ($couponStmt->rowCount() > 0) {
                    $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);

                    $couponId = $coupon['coupon_id'];
                    $discountAmount = ($subtotal * (float)$coupon['discount']) / 100;
                }
            }

            $totalAmount = ($subtotal - $discountAmount) + $shippingCost;
            $status = "Processing";
            $trackingNumber = "TRK" . time() . rand(100, 999);

            // Insert order
            $orderQuery = "INSERT INTO Orders
                           (order_date, user_id, total_amount, status, discount, shipping_cost, tracking_number, coupon_id)
                           VALUES
                           (CURDATE(), :user_id, :total_amount, :status, :discount, :shipping_cost, :tracking_number, :coupon_id)";

            $orderStmt = $this->conn->prepare($orderQuery);

            $orderStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
            $orderStmt->bindParam(":total_amount", $totalAmount);
            $orderStmt->bindParam(":status", $status);
            $orderStmt->bindParam(":discount", $discountAmount);
            $orderStmt->bindParam(":shipping_cost", $shippingCost);
            $orderStmt->bindParam(":tracking_number", $trackingNumber);
            if ($couponId === null) {
                $orderStmt->bindValue(":coupon_id", null, PDO::PARAM_NULL);
            } else {
                $orderStmt->bindValue(":coupon_id", $couponId, PDO::PARAM_INT);
            }

            $orderStmt->execute();

            $orderId = $this->conn->lastInsertId();

            // Insert order items and update stock
            foreach ($items as $item) {
                $itemTotal = ((int)$item['quantity'] * (float)$item['price']);

                $orderItemQuery = "INSERT INTO Order_Item
                                   (order_id, product_id, quantity, unit_price, total)
                                   VALUES
                                   (:order_id, :product_id, :quantity, :unit_price, :total)";

                $orderItemStmt = $this->conn->prepare($orderItemQuery);

                $orderItemStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
                $orderItemStmt->bindParam(":product_id", $item['product_id'], PDO::PARAM_INT);
                $orderItemStmt->bindParam(":quantity", $item['quantity'], PDO::PARAM_INT);
                $orderItemStmt->bindParam(":unit_price", $item['price']);
                $orderItemStmt->bindParam(":total", $itemTotal);

                $orderItemStmt->execute();

                $stockQuery = "UPDATE Product
                               SET stock_count = stock_count - :quantity_remove
                               WHERE product_id = :product_id
                               AND stock_count >= :quantity_check";

                $stockStmt = $this->conn->prepare($stockQuery);

                $stockStmt->bindValue(":quantity_remove", (int)$item['quantity'], PDO::PARAM_INT);
                $stockStmt->bindValue(":quantity_check", (int)$item['quantity'], PDO::PARAM_INT);
                $stockStmt->bindValue(":product_id", (int)$item['product_id'], PDO::PARAM_INT);

                $stockStmt->execute();

                if ($stockStmt->rowCount() == 0) {
                    $this->conn->rollBack();
                    $this->setLastError("Stock update failed for one product.");
                    return false;
                }
            }

            // Clear cart
            $clearCartQuery = "DELETE FROM Cart_Item
                               WHERE cart_id = :cart_id";

            $clearStmt = $this->conn->prepare($clearCartQuery);
            $clearStmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
            $clearStmt->execute();

            $this->conn->commit();

            return $orderId;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    // Get order by id
    public function getById($orderId) {
        $query = "SELECT *
                  FROM Orders
                  WHERE order_id = :order_id
                  AND user_id = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all orders for customer
    public function getUserOrders() {
        $query = "SELECT *
                  FROM Orders
                  WHERE user_id = :user_id
                  ORDER BY order_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get order items
    public function getOrderItems($orderId) {
        $query = "SELECT 
                    oi.order_item_id,
                    oi.order_id,
                    oi.product_id,
                    p.name AS product_name,
                    p.image,
                    p.brand,
                    p.category,
                    oi.quantity,
                    oi.unit_price,
                    oi.total
                  FROM Order_Item oi
                  INNER JOIN Product p ON oi.product_id = p.product_id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cancel order
    public function cancel($orderId) {
        try {
            $this->conn->beginTransaction();

            $orderQuery = "SELECT status
                           FROM Orders
                           WHERE order_id = :order_id
                           AND user_id = :user_id
                           LIMIT 1";

            $orderStmt = $this->conn->prepare($orderQuery);

            $orderStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
            $orderStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);

            $orderStmt->execute();

            if ($orderStmt->rowCount() == 0) {
                $this->conn->rollBack();
                return false;
            }

            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

            if ($order['status'] == "Cancelled" || $order['status'] == "Delivered" || $order['status'] == "Shipped") {
                $this->conn->rollBack();
                return false;
            }

            // Return products to stock
            $itemsQuery = "SELECT product_id, quantity
                           FROM Order_Item
                           WHERE order_id = :order_id";

            $itemsStmt = $this->conn->prepare($itemsQuery);
            $itemsStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
            $itemsStmt->execute();

            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $stockQuery = "UPDATE Product
                               SET stock_count = stock_count + :quantity
                               WHERE product_id = :product_id";

                $stockStmt = $this->conn->prepare($stockQuery);

                $stockStmt->bindParam(":quantity", $item['quantity'], PDO::PARAM_INT);
                $stockStmt->bindParam(":product_id", $item['product_id'], PDO::PARAM_INT);

                $stockStmt->execute();
            }

            // Update order status
            $updateQuery = "UPDATE Orders
                            SET status = 'Cancelled'
                            WHERE order_id = :order_id
                            AND user_id = :user_id";

            $updateStmt = $this->conn->prepare($updateQuery);

            $updateStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
            $updateStmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);

            $updateStmt->execute();

            $this->conn->commit();

            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Admin can update order status
    public function updateStatus($orderId, $status) {
        $allowedStatus = ["Pending Payment", "Payment Rejected", "Processing", "Shipped", "Delivered", "Cancelled"];

        if (!in_array($status, $allowedStatus)) {
            return false;
        }

        $query = "UPDATE Orders
                  SET status = :status
                  WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get order status
    public function getStatus($orderId) {
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

    // Get order summary
    public function getOrderSummary($orderId) {
        $order = $this->getById($orderId);

        if (!$order) {
            return false;
        }

        $items = $this->getOrderItems($orderId);

        return [
            "order_id" => $order['order_id'],
            "order_date" => $order['order_date'],
            "status" => $order['status'],
            "discount" => $order['discount'],
            "shipping_cost" => $order['shipping_cost'],
            "total_amount" => $order['total_amount'],
            "tracking_number" => $order['tracking_number'],
            "items" => $items
        ];
    }
}

?>
