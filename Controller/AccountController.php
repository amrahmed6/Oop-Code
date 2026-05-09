<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/customer.php";

class AccountController extends BaseController
{
    public function updateProfile()
    {
        $this->requireLogin();

        $userId = $this->post('user_id', $_SESSION['user_id']);
        $nameParts = explode(" ", trim($this->post('name')), 2);
        $firstName = $nameParts[0] ?? "";
        $lastName = $nameParts[1] ?? "";

        $user = new User($this->db);
        $user->updateProfile($userId, $firstName, $lastName, $this->post('phone'));

        $_SESSION['first_name'] = $firstName;
        $this->go("account.php");
    }

    public function updateAddress()
    {
        $this->requireLogin();

        $userId = $this->post('user_id', $_SESSION['user_id']);
        $customer = new Customer($this->db, $userId);
        $customer->updateDeliveryAddress($this->post('delivery_address'));

        $this->go("account.php");
    }

    public function changePassword()
    {
        $this->requireLogin();

        $user = new User($this->db);
        $changed = $user->changePassword(
            $this->post('user_id', $_SESSION['user_id']),
            $this->post('old_password'),
            $this->post('new_password')
        );

        $_SESSION['account_message'] = $changed
            ? "Password changed successfully"
            : "Old password is incorrect or new password is too short";

        $this->go("account.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new AccountController();

    switch ($action) {
        case 'update_profile':
            $controller->updateProfile();
            break;
        case 'update_address':
            $controller->updateAddress();
            break;
        case 'change_password':
            $controller->changePassword();
            break;
        default:
            http_response_code(400);
            echo 'Invalid account action';
            break;
    }
}
