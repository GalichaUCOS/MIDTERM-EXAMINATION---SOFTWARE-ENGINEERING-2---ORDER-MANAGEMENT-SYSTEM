<?php
session_start();
include('../config/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SuperAdmin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'Admin';
        
        $sql = "INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password_hash, $role);
        
        if ($stmt->execute()) {
            $message = "New Cashier **(" . htmlspecialchars($username) . ")** registered successfully!";
        } else {
            // Error handling for duplicate username
            $error = "Registration failed. Username may already be taken.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register Cashier</title>
        <link rel="stylesheet" href="../assets/css/styles.css">
    
<body>
    <?php include('../includes/header.php'); ?>
    
    <h2>Register New Admin (Cashier)</h2>
    
    <?php 
    if ($message) echo "<p style='color:green;'>$message</p>"; 
    if ($error) echo "<p style='color:red;'>$error</p>"; 
    ?>
    
    <form action="register_admin.php" method="POST">
        <label>Username for Cashier:</label>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Register Cashier Account</button>
    </form>
    <p><a href="index.php">Go back to Dashboard</a></p>
</body>
</html>