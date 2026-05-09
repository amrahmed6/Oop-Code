<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/wishlist.php";

class WishlistController extends BaseController
{
    public function addWishlist()
    {
        $this->requireLogin();

        $wishlist = new Wishlist($this->db);
        $wishlist->add($this->currentUserId(), $this->post('product_id'));

        $this->redirectTo($this->post('redirect_to', '../View/wishlist.php'));
    }

    public function removeWishlist()
    {
        $this->requireLogin();

        $wishlist = new Wishlist($this->db);
        $wishlist->remove($this->currentUserId(), $this->post('product_id'));

        $this->go("wishlist.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new WishlistController();

    switch ($action) {
        case 'add_wishlist':
            $controller->addWishlist();
            break;
        case 'remove_wishlist':
            $controller->removeWishlist();
            break;
        default:
            http_response_code(400);
            echo 'Invalid wishlist action';
            break;
    }
}
