<?php

require_once __DIR__ . "/BaseController.php";
require_once __DIR__ . "/../Model/product.php";

class ShopController extends BaseController
{
    public function showProducts()
    {
        $product = new Product($this->db);
        return $product->getAll();
    }
}
