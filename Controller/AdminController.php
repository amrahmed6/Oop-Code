<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/admin.php";
require_once __DIR__ . "/../Model/product.php";
require_once __DIR__ . "/../Model/order.php";
require_once __DIR__ . "/../Model/coupon.php";
require_once __DIR__ . "/../Model/payment.php";

class AdminController extends BaseController
{
    public function addProduct()
    {
        $this->requireAdmin();

        $adminId = $this->post('admin_id', $_SESSION['user_id']);
        $admin = new Admin($this->db, $adminId);

        $admin->addProduct(
            $this->post('name'),
            $this->post('description'),
            $this->post('brand'),
            $this->post('category'),
            $this->post('price'),
            $this->post('stock_count'),
            $this->post('image', null)
        );

        $this->go("admin-products.php");
    }

    public function updateProduct()
    {
        $this->requireAdmin();

        $product = new Product($this->db);
        $data = [];

        foreach (["name", "description", "brand", "category", "price", "stock_count", "image"] as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            }
        }

        $product->update($this->post('product_id'), $data);
        $this->go("admin-products.php");
    }

    public function deleteProduct()
    {
        $this->requireAdmin();

        $product = new Product($this->db);
        $product->delete($this->post('product_id'));

        $this->go("admin-products.php");
    }

    public function createCoupon()
    {
        $this->requireAdmin();

        $adminId = $this->post('admin_id', $_SESSION['user_id']);
        $admin = new Admin($this->db, $adminId);

        $admin->createCoupon(
            $this->post('code'),
            $this->post('discount'),
            $this->post('expiry_date')
        );

        $this->go("admin-coupons.php");
    }

    public function deleteCoupon()
    {
        $this->requireAdmin();

        $coupon = new Coupon($this->db);
        $coupon->delete($this->post('coupon_id'));

        $this->go("admin-coupons.php");
    }

    public function updateOrderStatus()
    {
        $this->requireAdmin();

        $order = new Order($this->db);
        $order->updateStatus($this->post('order_id'), $this->post('status'));

        $this->go("admin-orders.php");
    }


    public function approveInstapayPayment()
    {
        $this->requireAdmin();

        $payment = new Payment($this->db);
        $payment->approveInstapay($this->post('payment_id'), $_SESSION['user_id']);

        $this->go("admin-orders.php");
    }

    public function rejectInstapayPayment()
    {
        $this->requireAdmin();

        $payment = new Payment($this->db);
        $payment->rejectInstapay(
            $this->post('payment_id'),
            $_SESSION['user_id'],
            $this->post('admin_note', 'Transfer was not confirmed')
        );

        $this->go("admin-orders.php");
    }

    public function blockUser()
    {
        $this->requireAdmin();
        $this->setCustomerStatus($this->post('user_id'), 0);
        $this->go("admin-users.php");
    }

    public function unblockUser()
    {
        $this->requireAdmin();
        $this->setCustomerStatus($this->post('user_id'), 1);
        $this->go("admin-users.php");
    }

    public function deleteUser()
    {
        $this->requireAdmin();

        if ((int)$this->post('user_id') === (int)$_SESSION['user_id']) {
            $this->go("admin-users.php");
        }

        $query = "DELETE FROM Users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([":user_id" => $this->post('user_id')]);

        $this->go("admin-users.php");
    }

    private function setCustomerStatus($userId, $status)
    {
        $query = "UPDATE Customer
                  SET account_status = :status
                  WHERE customer_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ":status" => $status,
            ":user_id" => $userId
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new AdminController();

    switch ($action) {
        case 'add_product':
            $controller->addProduct();
            break;
        case 'update_product':
            $controller->updateProduct();
            break;
        case 'delete_product':
            $controller->deleteProduct();
            break;
        case 'create_coupon':
            $controller->createCoupon();
            break;
        case 'delete_coupon':
            $controller->deleteCoupon();
            break;
        case 'update_order_status':
            $controller->updateOrderStatus();
            break;
        case 'approve_instapay_payment':
            $controller->approveInstapayPayment();
            break;
        case 'reject_instapay_payment':
            $controller->rejectInstapayPayment();
            break;
        case 'block_user':
            $controller->blockUser();
            break;
        case 'unblock_user':
            $controller->unblockUser();
            break;
        case 'delete_user':
            $controller->deleteUser();
            break;
        default:
            http_response_code(400);
            echo 'Invalid admin action';
            break;
    }
}
