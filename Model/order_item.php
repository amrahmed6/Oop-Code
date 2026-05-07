<?php

class Order_Item {
    private $conn;
    private $table = "Order_Item";

    private $orderItemId;
    private $orderId;
    private $productId;
    private $quantity;
    private $unitPrice;
    private $total;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create order item
    public function create($orderId, $productId, $quantity, $unitPrice) {
        if ($quantity <= 0 || $unitPrice < 0) {
            return false;
        }

        $total = $quantity * $unitPrice;

        $query = "INSERT INTO " . $this->table . "
                  (order_id, product_id, quantity, unit_price, total)
                  VALUES
                  (:order_id, :product_id, :quantity, :unit_price, :total)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":unit_price", $unitPrice);
        $stmt->bindParam(":total", $total);

        return $stmt->execute();
    }

    // Get one order item by id
    public function getById($orderItemId) {
        $query = "SELECT 
                    oi.order_item_id,
                    oi.order_id,
                    oi.product_id,
                    p.name AS product_name,
                    p.image,
                    oi.quantity,
                    oi.unit_price,
                    oi.total
                  FROM " . $this->table . " oi
                  INNER JOIN Product p ON oi.product_id = p.product_id
                  WHERE oi.order_item_id = :order_item_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_item_id", $orderItemId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all items for specific order
    public function getByOrderId($orderId) {
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
                  FROM " . $this->table . " oi
                  INNER JOIN Product p ON oi.product_id = p.product_id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update quantity and recalculate total
    public function updateQuantity($orderItemId, $quantity) {
        if ($quantity <= 0) {
            return false;
        }

        $item = $this->getById($orderItemId);

        if (!$item) {
            return false;
        }

        $total = $item['unit_price'] * $quantity;

        $query = "UPDATE " . $this->table . "
                  SET quantity = :quantity,
                      total = :total
                  WHERE order_item_id = :order_item_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":total", $total);
        $stmt->bindParam(":order_item_id", $orderItemId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete order item
    public function delete($orderItemId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE order_item_id = :order_item_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_item_id", $orderItemId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get total for one order item
    public function getTotal($orderItemId) {
        $query = "SELECT total
                  FROM " . $this->table . "
                  WHERE order_item_id = :order_item_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_item_id", $orderItemId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['total'] : 0;
    }

    // Get total amount for whole order
    public function getOrderTotal($orderId) {
        $query = "SELECT COALESCE(SUM(total), 0) AS order_total
                  FROM " . $this->table . "
                  WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['order_total'] : 0;
    }
}

?>