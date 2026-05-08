<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";

require_once __DIR__ . "/../Model/User.php";
require_once __DIR__ . "/../Model/Customer.php";
require_once __DIR__ . "/../Model/Admin.php";
require_once __DIR__ . "/../Model/Product.php";
require_once __DIR__ . "/../Model/Cart.php";
require_once __DIR__ . "/../Model/Order.php";
require_once __DIR__ . "/../Model/Payment.php";
require_once __DIR__ . "/../Model/Wishlist.php";
require_once __DIR__ . "/../Model/Review.php";
require_once __DIR__ . "/../Model/Coupon.php";

$database = new Database();
$db = $database->connect();

if (!$db) {
    die("Database connection failed");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("POST only");
}

$action = $_POST['action'] ?? "";

function go($page) {
    header("Location: ../View/" . $page);
    exit;
}

function redirectTo($url) {
    header("Location: " . $url);
    exit;
}

switch ($action) {

    // ======================
    // Register
    // ======================
    case "register":

        if ($_POST['password'] !== $_POST['confirm_password']) {
            $_SESSION['register_error'] = "Passwords do not match";
            go("register.php");
        }

        $user = new User($db);

        if ($user->emailExists($_POST['email'])) {
            $_SESSION['register_error'] = "Email already exists";
            go("register.php");
        }

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
            $_SESSION['email'] = $_POST['email'];
            $_SESSION['first_name'] = $_POST['first_name'];
            $_SESSION['role'] = "customer";

            go("index.php");
        } else {
            $_SESSION['register_error'] = "Registration failed";
            go("register.php");
        }

        break;


    // ======================
    // Login
    // ======================
    case "login":

        $user = new User($db);

        $loggedUser = $user->login(
            $_POST['email'],
            $_POST['password']
        );

        if ($loggedUser) {
            $_SESSION['user_id'] = $loggedUser['user_id'];
            $_SESSION['email'] = $loggedUser['email'];
            $_SESSION['first_name'] = $loggedUser['first_name'];

            $admin = new Admin($db);

            if ($admin->isAdmin($loggedUser['user_id'])) {
                $_SESSION['role'] = "admin";
                go("admin.php");
            } else {
                $_SESSION['role'] = "customer";
                go("index.php");
            }
        } else {
            $_SESSION['login_error'] = "Invalid email or password";
            go("login.php");
        }

        break;


    // ======================
    // Logout
    // ======================
    case "logout":

        session_unset();
        session_destroy();

        go("login.php");

        break;


    // ======================
    // Forgot Password
    // ======================
    case "forgot_password":

        $user = new User($db);

        if ($user->emailExists($_POST['email'])) {
            $_SESSION['forgot_message'] = "Reset link sent successfully.";
        } else {
            $_SESSION['forgot_message'] = "Email not found.";
        }

        go("forgot.php");

        break;


    // ======================
    // Update Profile
    // ======================
    case "update_profile":

        $userId = $_POST['user_id'];

        $nameParts = explode(" ", trim($_POST['name']), 2);
        $firstName = $nameParts[0] ?? "";
        $lastName = $nameParts[1] ?? "";

        $user = new User($db);

        $user->updateProfile(
            $userId,
            $firstName,
            $lastName,
            $_POST['phone']
        );

        go("account.php");

        break;


    // ======================
    // Update Address
    // ======================
    case "update_address":

        $customer = new Customer($db, $_POST['user_id']);

        $customer->updateDeliveryAddress($_POST['delivery_address']);

        go("account.php");

        break;


    // ======================
    // Change Password
    // ======================
    case "change_password":

        $user = new User($db);

        $changed = $user->changePassword(
            $_POST['user_id'],
            $_POST['old_password'],
            $_POST['new_password']
        );

        if ($changed) {
            $_SESSION['account_message'] = "Password changed successfully";
        } else {
            $_SESSION['account_message'] = "Old password is incorrect";
        }

        go("account.php");

        break;


    // ======================
    // Add Product
    // ======================
    case "add_product":

        $admin = new Admin($db, $_POST['admin_id']);

        $admin->addProduct(
            $_POST['name'],
            $_POST['description'],
            $_POST['brand'],
            $_POST['category'],
            $_POST['price'],
            $_POST['stock_count'],
            $_POST['image'] ?? null
        );

        go("admin-products.php");

        break;


    // ======================
    // Update Product
    // ======================
    case "update_product":

        $product = new Product($db);

        $data = [];

        if (isset($_POST['name'])) {
            $data['name'] = $_POST['name'];
        }

        if (isset($_POST['description'])) {
            $data['description'] = $_POST['description'];
        }

        if (isset($_POST['brand'])) {
            $data['brand'] = $_POST['brand'];
        }

        if (isset($_POST['category'])) {
            $data['category'] = $_POST['category'];
        }

        if (isset($_POST['price'])) {
            $data['price'] = $_POST['price'];
        }

        if (isset($_POST['stock_count'])) {
            $data['stock_count'] = $_POST['stock_count'];
        }

        if (isset($_POST['image'])) {
            $data['image'] = $_POST['image'];
        }

        $product->update($_POST['product_id'], $data);

        go("admin-products.php");

        break;


    // ======================
    // Delete Product
    // ======================
    case "delete_product":

        $product = new Product($db);

        $product->delete($_POST['product_id']);

        go("admin-products.php");

        break;


    // ======================
    // Create Coupon
    // ======================
    case "create_coupon":

        $admin = new Admin($db, $_POST['admin_id']);

        $admin->createCoupon(
            $_POST['code'],
            $_POST['discount'],
            $_POST['expiry_date']
        );

        go("admin-coupons.php");

        break;


    // ======================
    // Delete Coupon
    // ======================
    case "delete_coupon":

        $coupon = new Coupon($db);

        $coupon->delete($_POST['coupon_id']);

        go("admin-coupons.php");

        break;


    // ======================
    // Update Order Status
    // ======================
    case "update_order_status":

        $order = new Order($db);

        $order->updateStatus(
            $_POST['order_id'],
            $_POST['status']
        );

        go("admin-orders.php");

        break;


    // ======================
    // Block User
    // ======================
    case "block_user":

        $query = "UPDATE Customer 
                  SET account_status = 0 
                  WHERE customer_id = :user_id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_POST['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        go("admin-users.php");

        break;


    // ======================
    // Unblock User
    // ======================
    case "unblock_user":

        $query = "UPDATE Customer 
                  SET account_status = 1 
                  WHERE customer_id = :user_id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_POST['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        go("admin-users.php");

        break;


    // ======================
    // Delete User
    // ======================
    case "delete_user":

        $query = "DELETE FROM Users 
                  WHERE user_id = :user_id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_POST['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        go("admin-users.php");

        break;


    // ======================
    // Add To Cart
    // ======================
    case "add_to_cart":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'] ?? 1;

        $cart = new Cart($db, $userId, session_id());

        $cart->addItem($productId, $quantity);

        $redirect = $_POST['redirect_to'] ?? "../View/cart.php";

        redirectTo($redirect);

        break;


    // ======================
    // Update Cart Quantity
    // ======================
    case "update_cart_quantity":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];

        $cart = new Cart($db, $userId, session_id());

        $cart->updateQuantity(
            $_POST['cart_item_id'],
            $_POST['quantity']
        );

        go("cart.php");

        break;


    // ======================
    // Remove Cart Item
    // ======================
    case "remove_cart_item":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];

        $cart = new Cart($db, $userId, session_id());

        $cart->removeItem($_POST['cart_item_id']);

        go("cart.php");

        break;


    // ======================
    // Add Wishlist
    // ======================
    case "add_wishlist":

        $wishlist = new Wishlist($db);

        $wishlist->add(
            $_POST['user_id'],
            $_POST['product_id']
        );

        $redirect = $_POST['redirect_to'] ?? "../View/wishlist.php";

        redirectTo($redirect);

        break;


    // ======================
    // Remove Wishlist
    // ======================
    case "remove_wishlist":

        $wishlist = new Wishlist($db);

        $wishlist->remove(
            $_POST['user_id'],
            $_POST['product_id']
        );

        go("wishlist.php");

        break;


    // ======================
    // Add Review
    // ======================
    case "add_review":

        $review = new Review($db);

        $review->create(
            $_POST['product_id'],
            $_POST['user_id'],
            $_POST['rating'],
            $_POST['comment']
        );

        redirectTo("../View/product.php?id=" . $_POST['product_id']);

        break;


    // ======================
    // Create Order
    // ======================
    case "create_order":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];

        $order = new Order($db, $userId);

        $orderId = $order->createOrder(
            $_POST['shipping_cost'] ?? 0,
            $_POST['coupon_code'] ?? null
        );

        if ($orderId) {
            redirectTo("../View/payment.php?order_id=" . $orderId);
        } else {
            echo "Order failed";
        }

        break;


    // ======================
    // Cancel Order
    // ======================
    case "cancel_order":

        $userId = $_SESSION['user_id'] ?? $_POST['user_id'];

        $order = new Order($db, $userId);

        $order->cancel($_POST['order_id']);

        go("orders.php");

        break;


    // ======================
    // Create Payment
    // ======================
    case "create_payment":

        $payment = new Payment($db);

        $result = $payment->create(
            $_POST['order_id'],
            $_POST['payment_method'],
            null,
            "Completed"
        );

        if ($result) {
            redirectTo("../View/success.php?order_id=" . $_POST['order_id']);
        } else {
            echo "Payment failed or already exists";
        }

        break;


    // ======================
    // Old Payment Action Support
    // ======================
    case "payment":

        $payment = new Payment($db);

        $result = $payment->create(
            $_POST['order_id'],
            $_POST['payment_method'],
            null,
            "Completed"
        );

        if ($result) {
            redirectTo("../View/success.php?order_id=" . $_POST['order_id']);
        } else {
            echo "Payment failed or already exists";
        }

        break;


    default:
        echo "Invalid action";
        break;
}

?>