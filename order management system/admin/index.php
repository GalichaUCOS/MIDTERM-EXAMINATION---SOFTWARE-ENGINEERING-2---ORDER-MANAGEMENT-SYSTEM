<?php
session_start();
include('../config/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SuperAdmin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Super Admin Dashboard - Order Management System</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <h2>Welcome, Super Administrator (<?= htmlspecialchars($_SESSION['username']); ?>)</h2>

    <div class="dashboard-grid">
        <div class="card">
            <h3>User Management</h3>
            <p>Manage user accounts, suspend or activate users.</p>
            <a href="manage_users.php">Manage Accounts</a>
        </div>
        <div class="card">
            <h3>Admin Registration</h3>
            <p>Register new Admin or Cashier accounts.</p>
            <a href="register_admin.php">Register New Admin/Cashier</a>
        </div>
        <div class="card">
            <h3>Product Management</h3>
            <p>Add or edit menu items and products.</p>
            <a href="../cashier/manage_products.php">Manage Products</a>
        </div>
    </div>
</body>
</html>
