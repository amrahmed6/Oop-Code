<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../Model/Database.php";

class BaseController
{
    protected $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        if (!$this->db) {
            die("Database connection failed");
        }
    }

    protected function go($page)
    {
        header("Location: ../View/" . $page);
        exit;
    }

    protected function redirectTo($url)
    {
        header("Location: " . $url);
        exit;
    }

    protected function post($key, $default = "")
    {
        return $_POST[$key] ?? $default;
    }

    protected function currentUserId()
    {
        return $_SESSION['user_id'] ?? $this->post('user_id', null);
    }

    protected function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            $this->go("login.php");
        }
    }

    protected function requireAdmin()
    {
        $this->requireLogin();

        require_once __DIR__ . "/../Model/user.php";
        require_once __DIR__ . "/../Model/admin.php";

        $admin = new Admin($this->db, $_SESSION['user_id']);

        if (!$admin->isAdmin($_SESSION['user_id'])) {
            $this->go("index.php");
        }
    }
}
