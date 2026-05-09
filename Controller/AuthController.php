<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/customer.php";
require_once __DIR__ . "/../Model/admin.php";

class AuthController extends BaseController
{
    public function register()
    {
        if ($this->post('password') !== $this->post('confirm_password')) {
            $_SESSION['register_error'] = "Passwords do not match";
            $this->go("register.php");
        }

        $user = new User($this->db);

        if ($user->emailExists($this->post('email'))) {
            $_SESSION['register_error'] = "Email already exists";
            $this->go("register.php");
        }

        $created = $user->registration(
            $this->post('email'),
            $this->post('password'),
            $this->post('first_name'),
            $this->post('last_name'),
            $this->post('phone')
        );

        if (!$created) {
            $_SESSION['register_error'] = "Registration failed";
            $this->go("register.php");
        }

        $userId = $this->db->lastInsertId();
        $customer = new Customer($this->db, $userId);

        $customer->createCustomer(
            $userId,
            trim($this->post('first_name') . " " . $this->post('last_name')),
            $this->post('delivery_address'),
            true
        );

        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $this->post('email');
        $_SESSION['first_name'] = $this->post('first_name');
        $_SESSION['role'] = "customer";

        $this->go("index.php");
    }

    public function login()
    {
        $user = new User($this->db);
        $loggedUser = $user->login($this->post('email'), $this->post('password'));

        if (!$loggedUser) {
            $_SESSION['login_error'] = "Invalid email or password";
            $this->go("login.php");
        }

        $_SESSION['user_id'] = $loggedUser['user_id'];
        $_SESSION['email'] = $loggedUser['email'];
        $_SESSION['first_name'] = $loggedUser['first_name'];

        $admin = new Admin($this->db);

        if ($admin->isAdmin($loggedUser['user_id'])) {
            $_SESSION['role'] = "admin";
            $this->go("admin.php");
        }

        $_SESSION['role'] = "customer";
        $this->go("index.php");
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->go("login.php");
    }

    public function forgotPassword()
    {
        $user = new User($this->db);

        if ($user->emailExists($this->post('email'))) {
            $_SESSION['forgot_message'] = "Reset link sent successfully.";
        } else {
            $_SESSION['forgot_message'] = "Email not found.";
        }

        $this->go("forgot.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new AuthController();

    switch ($action) {
        case 'register':
            $controller->register();
            break;
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'forgot_password':
            $controller->forgotPassword();
            break;
        default:
            http_response_code(400);
            echo 'Invalid auth action';
            break;
    }
}
