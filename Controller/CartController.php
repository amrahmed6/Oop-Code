<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/cart.php";

class CartController extends BaseController
{
    public function addToCart()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->addItem($this->post('product_id'), $this->post('quantity', 1));

        $this->redirectTo($this->post('redirect_to', '../View/cart.php'));
    }

    public function updateQuantity()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->updateQuantity($this->post('cart_item_id'), $this->post('quantity'));

        $this->go("cart.php");
    }

    public function removeItem()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->removeItem($this->post('cart_item_id'));

        $this->go("cart.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new CartController();

    switch ($action) {
        case 'add_to_cart':
            $controller->addToCart();
            break;
        case 'update_cart_quantity':
            $controller->updateQuantity();
            break;
        case 'remove_cart_item':
            $controller->removeItem();
            break;
        default:
            http_response_code(400);
            echo 'Invalid cart action';
            break;
    }
}
