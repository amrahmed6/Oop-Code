<?php

session_start();

require_once "../View/Database.php";
require_once "../Model/User.php";
require_once "../Model/Customer.php";
require_once "../Model/Admin.php";
require_once "../Model/Product.php";
require_once "../Model/Cart.php";
require_once "../Model/Order.php";
require_once "../Model/Payment.php";

$database = new Database();
$db = $database->connect();

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("POST only");
}

switch ($action) {

    // Register Customer
    case "register":

        $user = new User($db);

        $result = $user->registration(
            $_POST['email'],
            $_POST['password'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['phone']
        );

        if ($result) {
            $userId = $db->lastInsertId();

            $customer = new Customer($db, $userId);
            $customer->createCustomer(
                $userId,
                $_POST['first_name'] . " " . $_POST['last_name'],
                $_POST['delivery_address'],
                true
            );

            $_SESSION['user_id'] = $userId;

            echo "Customer registered successfully";
        } else {
            echo "Registration failed";
        }

        break;


    // Login
    case "login":

        $user = new User($db);

        $loggedUser = $user->login(
            $_POST['email'],
            $_POST['password']
        );

        if ($loggedUser) {
            $_SESSION['user_id'] = $loggedUser['user_id'];
            $_SESSION['email'] = $loggedUser['email'];

            echo "Login successful";
        } else {
            echo "Invalid email or password";
        }

        break;


    // Add Product by Admin
    case "add_product":

        $admin = new Admin($db, $_POST['admin_id']);

        $result = $admin->addProduct(
            $_POST['name'],
            $_POST['description'],
            $_POST['brand'],
            $_POST['category'],
            $_POST['price'],
            $_POST['stock_count'],
            $_POST['image'] ?? null
        );

        if ($result) {
            echo "Product added successfully";
        } else {
            echo "Add product failed";
        }

        break;


    // Add To Cart
    case "add_to_cart":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];

        $cart = new Cart($db, $userId, session_id());

        $result = $cart->addItem(
            $_POST['product_id'],
            $_POST['quantity']
        );

        if ($result) {
            echo "Product added to cart";
        } else {
            echo "Add to cart failed";
        }

        break;


    // Create Order
    case "create_order":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];

        $order = new Order($db, $userId);

        $orderId = $order->createOrder(
            $_POST['shipping_cost'] ?? 0,
            $_POST['coupon_code'] ?? null
        );

        if ($orderId) {
            echo "Order created successfully. Order ID: " . $orderId;
        } else {
            echo "Order failed";
        }

        break;


    // Payment
    case "payment":

        $payment = new Payment($db);

        $result = $payment->create(
            $_POST['order_id'],
            $_POST['payment_method']
        );

        if ($result) {
            echo "Payment created successfully";
        } else {
            echo "Payment failed";
        }

        break;


    default:
        echo "Invalid action";
        break;
}

?>