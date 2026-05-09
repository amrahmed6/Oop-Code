<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/order.php";

class OrderController extends BaseController
{
    public function createOrder()
    {
        $this->requireLogin();

        $order = new Order($this->db, $this->currentUserId());
        $orderId = $order->createOrder(
            $this->post('shipping_cost', 0),
            $this->post('coupon_code', null),
            session_id()
        );

        if ($orderId) {
            unset($_SESSION['order_error']);
            $this->redirectTo("../View/payment.php?order_id=" . $orderId);
        }

        $_SESSION['order_error'] = $order->getLastError() ?: "Order failed. Please check your cart and try again.";
        $this->go("checkout.php");
    }

    public function cancelOrder()
    {
        $this->requireLogin();

        $order = new Order($this->db, $this->currentUserId());
        $cancelled = $order->cancel($this->post('order_id'));

        if (!$cancelled) {
            $_SESSION['order_error'] = "Order could not be cancelled.";
        }

        $this->go("orders.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new OrderController();

    switch ($action) {
        case 'create_order':
            $controller->createOrder();
            break;
        case 'cancel_order':
            $controller->cancelOrder();
            break;
        default:
            http_response_code(400);
            echo 'Invalid order action';
            break;
    }
}
