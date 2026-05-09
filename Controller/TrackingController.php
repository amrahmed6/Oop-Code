<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/order.php";

class TrackingController extends BaseController
{
    public function trackOrder($orderId)
    {
        $order = new Order($this->db, $this->currentUserId());
        return $order->getById($orderId);
    }
}
