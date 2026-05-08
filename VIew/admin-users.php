<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/admin.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$adminId = $_SESSION['user_id'];

$admin = new Admin($db, $adminId);

if (!$admin->isAdmin($adminId)) {
    header("Location: index.php");
    exit;
}

$query = "SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            c.account_status,
            a.admin_id
          FROM Users u
          LEFT JOIN Customer c ON u.user_id = c.customer_id
          LEFT JOIN Admin a ON u.user_id = a.admin_id
          ORDER BY u.user_id DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Users Management | BonnaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="header">
  <a class="logo" href="index.php">Bonna<span>Verse</span></a>

  <div class="search">
    <input id="searchInput" placeholder="Search sneakers, apparel, brands..." />
  </div>

  <nav>
    <a href="admin.php">Dashboard</a>
    <a href="admin-products.php">Products</a>
    <a href="admin-orders.php">Orders</a>
    <a href="admin-users.php">Users</a>
    <a href="admin-coupons.php">Coupons</a>
    <a href="reports.php">Reports</a>
    <a href="shop.php">Shop</a>

    <form method="POST" action="../Controller/test.php" class="inline-form">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>Users Management</h1>

  <section class="panel">
    <input placeholder="Search user" id="userSearch">

    <br><br>

    <table>
      <tr>
        <th>User</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>

      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>

          <?php
            $role = $user['admin_id'] ? "Admin" : "Customer";

            if ($role == "Admin") {
                $status = "Active";
            } else {
                $status = $user['account_status'] ? "Active" : "Blocked";
            }
          ?>

          <tr>
            <td>
              <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>
            </td>

            <td>
              <?php echo htmlspecialchars($user['email']); ?>
            </td>

            <td>
              <?php echo htmlspecialchars($user['phone']); ?>
            </td>

            <td>
              <?php echo $role; ?>
            </td>

            <td>
              <?php echo $status; ?>
            </td>

            <td>
              <?php if ($role == "Customer"): ?>

                <?php if ($status == "Active"): ?>
                  <form method="POST" action="../Controller/test.php" class="inline-form">
                    <input type="hidden" name="action" value="block_user">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <button type="submit" class="btn outline">Block</button>
                  </form>
                <?php else: ?>
                  <form method="POST" action="../Controller/test.php" class="inline-form">
                    <input type="hidden" name="action" value="unblock_user">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <button type="submit" class="btn outline">Unblock</button>
                  </form>
                <?php endif; ?>

              <?php endif; ?>

              <?php if ($user['user_id'] != $adminId): ?>
                <form method="POST" action="../Controller/test.php" class="inline-form">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                  <button type="submit" class="btn outline">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>

        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6">No users found</td>
        </tr>
      <?php endif; ?>

    </table>
  </section>

</main>

<footer class="footer">
  <div>
    <b>BonnaVerse</b>
    <p>Simple multi-brand marketplace front-end.</p>
  </div>

  <div>
    <b>Links</b>
    <p>Shop · Orders · Account · Support</p>
  </div>

  <div>
    <b>Brands</b>
    <p>Nike · Adidas · Jordan · Supreme · Yeezy</p>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>