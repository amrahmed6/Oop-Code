<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/review.php";

class ReviewController extends BaseController
{
    public function addReview()
    {
        $this->requireLogin();

        $review = new Review($this->db);
        $review->create(
            $this->post('product_id'),
            $this->currentUserId(),
            $this->post('rating'),
            $this->post('comment')
        );

        $this->redirectTo("../View/product.php?id=" . $this->post('product_id'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action = $_POST['action'] ?? '';
    $controller = new ReviewController();

    switch ($action) {
        case 'add_review':
            $controller->addReview();
            break;
        default:
            http_response_code(400);
            echo 'Invalid review action';
            break;
    }
}
