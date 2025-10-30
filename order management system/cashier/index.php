<?php
session_start();
include('../config/db_connect.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT product_id, name, price, image_path FROM products ORDER BY name";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>POS System - Cashier - Order Management System</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="pos-container">
        <div class="menu-container">
            <div class="menu-header">
                <h2>Menu Items</h2>
                <div class="menu-actions">
                    <div class="search-box">
                        <input type="text" id="menuSearch" placeholder="Search menu items...">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>
                </div>
            </div>
            <div class="menu-grid">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="menu-item" data-product-id="<?= htmlspecialchars($product['product_id']); ?>">
                        <img src="../<?= htmlspecialchars($product['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?= htmlspecialchars($product['name']); ?>" onerror="this.src='../assets/images/placeholder.jpg'" style="width: 100%; height: 120px; object-fit: cover; margin-bottom: 10px; border-radius: 3px;">
                        <h3><?= htmlspecialchars($product['name']); ?></h3>
                        <p><?= number_format($product['price'], 2); ?> PHP</p>

                        <input type="number" class="qty-input" value="1" min="1">
                        <button onclick="addToOrder(this)">Add to order</button>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products available. Please add products via the admin panel.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="order-container">
            <h2>Ordered Items</h2>
            <div id="ordered-items-list">
            </div>

            <hr>
            <div style="margin-top: 20px;">
                <h3>Total: <span id="order-total">0.00 PHP</span></h3>
                <input type="number" id="cash-tendered" placeholder="Enter cash amount" step="0.01" min="0">
                <button id="pay-button">Process Payment</button>
                <div id="change-display" style="margin-top: 10px; font-weight: bold;"></div>
            </div>
        </div>
    </div>

    <script src="../assets/js/scripts.js"></script>
</body>
</html>
