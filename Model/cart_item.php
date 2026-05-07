<?php

class Cart_Item {
    private $conn;
    private $table = "Cart_Item";

    private $cartItemId;
    private $cartId;
    private $productId;
    private $quantity;
    private $price;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add item to cart
    public function create($cartId, $productId, $quantity, $price) {
        $query = "INSERT INTO " . $this->table . "
                  (cart_id, product_id, quantity, price)
                  VALUES
                  (:cart_id, :product_id, :quantity, :price)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price", $price);

        return $stmt->execute();
    }

    // Get one cart item
    public function getById($cartItemId) {
        $query = "SELECT 
                    ci.cart_item_id,
                    ci.cart_id,
                    ci.product_id,
                    p.name AS product_name,
                    p.image,
                    ci.quantity,
                    ci.price,
                    ci.quantity * ci.price AS total
                  FROM " . $this->table . " ci
                  INNER JOIN Product p ON ci.product_id = p.product_id
                  WHERE ci.cart_item_id = :cart_item_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all items in specific cart
    public function getByCartId($cartId) {
        $query = "SELECT 
                    ci.cart_item_id,
                    ci.cart_id,
                    ci.product_id,
                    p.name AS product_name,
                    p.image,
                    ci.quantity,
                    ci.price,
                    ci.quantity * ci.price AS total
                  FROM " . $this->table . " ci
                  INNER JOIN Product p ON ci.product_id = p.product_id
                  WHERE ci.cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update quantity
    public function updateQuantity($cartItemId, $quantity) {
        if ($quantity <= 0) {
            return $this->delete($cartItemId);
        }

        $query = "UPDATE " . $this->table . "
                  SET quantity = :quantity
                  WHERE cart_item_id = :cart_item_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete item from cart
    public function delete($cartItemId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE cart_item_id = :cart_item_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get total for one item
    public function getTotal($cartItemId) {
        $query = "SELECT quantity * price AS total
                  FROM " . $this->table . "
                  WHERE cart_item_id = :cart_item_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['total'] : 0;
    }

    // Check if product already exists in cart
    public function itemExists($cartId, $productId) {
        $query = "SELECT cart_item_id, quantity
                  FROM " . $this->table . "
                  WHERE cart_id = :cart_id
                  AND product_id = :product_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":cart_id", $cartId, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>