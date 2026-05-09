<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/payment.php";
require_once __DIR__ . "/../Model/order.php";

class PaymentController extends BaseController
{
    private function fail($orderId, $message)
    {
        $_SESSION['payment_error'] = $message;
        $this->go("payment.php?order_id=" . urlencode($orderId));
    }

    private function validateVisaData($orderId)
    {
        $cardholderName = trim($this->post('cardholder_name'));
        $cardNumber = preg_replace('/\D+/', '', $this->post('card_number'));
        $expiryMonth = (int)$this->post('expiry_month');
        $expiryYear = (int)$this->post('expiry_year');
        $cvv = preg_replace('/\D+/', '', $this->post('card_cvv'));

        if ($cardholderName === '') {
            $this->fail($orderId, "Please enter the cardholder name.");
        }

        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            $this->fail($orderId, "Please enter a valid Visa card number.");
        }

        if ($expiryMonth < 1 || $expiryMonth > 12) {
            $this->fail($orderId, "Please choose a valid expiry month.");
        }

        if ($expiryYear < (int)date('Y')) {
            $this->fail($orderId, "Please choose a valid expiry year.");
        }

        if (strlen($cvv) < 3 || strlen($cvv) > 4) {
            $this->fail($orderId, "Please enter a valid CVV.");
        }

        return [
            'cardholder_name' => $cardholderName,
            'card_last4' => substr($cardNumber, -4),
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear
        ];
    }

    private function uploadInstapayProof($orderId)
    {
        if (!isset($_FILES['transfer_proof']) || $_FILES['transfer_proof']['error'] !== UPLOAD_ERR_OK) {
            $this->fail($orderId, "Please upload the Instapay transfer screenshot.");
        }

        if ($_FILES['transfer_proof']['size'] > 5 * 1024 * 1024) {
            $this->fail($orderId, "Transfer screenshot must be less than 5 MB.");
        }

        $fileName = $_FILES['transfer_proof']['name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            $this->fail($orderId, "Only JPG, PNG, or WEBP images are allowed.");
        }

        if (getimagesize($_FILES['transfer_proof']['tmp_name']) === false) {
            $this->fail($orderId, "Please upload a valid image for the Instapay transfer proof.");
        }

        $uploadDir = __DIR__ . "/../View/uploads/instapay/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName = "instapay_order_" . (int)$orderId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
        $fullPath = $uploadDir . $safeName;

        if (!move_uploaded_file($_FILES['transfer_proof']['tmp_name'], $fullPath)) {
            $this->fail($orderId, "Could not save the transfer screenshot.");
        }

        return "uploads/instapay/" . $safeName;
    }

    public function createPayment()
    {
        $this->requireLogin();

        $orderId = (int)$this->post('order_id');
        $method = $this->post('payment_method');
        $allowedMethods = ["Visa", "Instapay", "Cash"];

        if ($orderId <= 0 || !in_array($method, $allowedMethods)) {
            $this->fail($orderId, "Invalid payment data.");
        }

        // Make sure the order belongs to the current logged-in customer.
        $orderModel = new Order($this->db, $_SESSION['user_id']);
        $order = $orderModel->getById($orderId);
        if (!$order) {
            $this->go("orders.php");
        }

        $payment = new Payment($this->db);
        if ($payment->getByOrderId($orderId)) {
            $this->redirectTo("../View/success.php?order_id=" . $orderId);
        }

        if ($method === "Instapay") {
            $senderPhone = trim($this->post('instapay_phone'));
            if ($senderPhone === '') {
                $this->fail($orderId, "Please enter the phone number used for the Instapay transfer.");
            }

            $proofPath = $this->uploadInstapayProof($orderId);
            $transactionId = "INSTAPAY" . time() . rand(1000, 9999);
            $paymentId = $payment->create($orderId, $method, $transactionId, "Pending");

            if (!$paymentId) {
                $this->fail($orderId, "Payment failed or already exists.");
            }

            if (!$payment->createInstapayTransfer($paymentId, $orderId, $senderPhone, $proofPath)) {
                $this->fail($orderId, "Could not save Instapay transfer details.");
            }

            $orderModel->updateStatus($orderId, "Pending Payment");
            $_SESSION['payment_success'] = "Instapay proof uploaded. Your order is waiting for admin approval.";
            $this->redirectTo("../View/success.php?order_id=" . $orderId);
        }

        if ($method === "Visa") {
            $visaData = $this->validateVisaData($orderId);
            $transactionId = "VISA" . time() . $visaData['card_last4'];
            $paymentId = $payment->create($orderId, $method, $transactionId, "Completed");

            if (!$paymentId) {
                $this->fail($orderId, "Payment failed or already exists.");
            }

            if (!$payment->createVisaDetails(
                $paymentId,
                $orderId,
                $visaData['cardholder_name'],
                $visaData['card_last4'],
                $visaData['expiry_month'],
                $visaData['expiry_year']
            )) {
                $this->fail($orderId, "Could not save Visa payment details.");
            }

            $this->redirectTo("../View/success.php?order_id=" . $orderId);
        }

        // Simple simulation for Cash in this student project.
        $paymentId = $payment->create($orderId, $method, null, "Completed");

        if ($paymentId) {
            $this->redirectTo("../View/success.php?order_id=" . $orderId);
        }

        $this->fail($orderId, "Payment failed or already exists.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new PaymentController();

    switch ($action) {
        case 'create_payment':
        case 'payment':
            $controller->createPayment();
            break;
        default:
            http_response_code(400);
            echo 'Invalid payment action';
            break;
    }
}
